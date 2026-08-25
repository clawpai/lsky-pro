<?php

declare(strict_types=1);

namespace App\Services;

use App\CouponType;
use App\DTOs\CreateOrderDto;
use App\Exceptions\ServiceException;
use App\Factories\PaymentDriverFactory;
use App\Jobs\CancelOrderJob;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PlanPrice;
use App\OrderStatus;
use App\OrderType;
use App\PaymentChannel;
use App\PaymentMethod;
use App\PaymentProvider;
use App\PlanType;
use App\UserCapacityFrom;
use App\UserGroupFrom;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderService
{
    /**
     * 创建订单
     *
     * @param int $priceId
     * @param string|null $couponCode
     * @param array $appends
     * @return Order
     * @throws ServiceException|Throwable
     */
    public function create(int $priceId, ?string $couponCode = null, array $appends = []): Order
    {
        $order = DB::transaction(function () use ($priceId, $couponCode, $appends) {
            $data = $this->preview($priceId, $couponCode);

            /** @var Order $order */
            $order = Order::create([...$data, ...$appends, ...[
                'type' => OrderType::Plan,
                'status' => OrderStatus::Unpaid,
            ]]);

            // 价格为0则直接支付成功
            if ($order->amount <= 0) {
                \App\Facades\OrderService::complete($order);
            }

            return $order;
        });

        // 延时取消订单
        if ($order->status === OrderStatus::Unpaid) {
            dispatch(new CancelOrderJob($order));
        }

        return $order;
    }

    /**
     * 预览订单
     *
     * @param int $priceId
     * @param string|null $couponCode
     * @return array
     * @throws ServiceException
     */
    public function preview(int $priceId, ?string $couponCode = null): array
    {
        $couponId = null;
        $deductAmount = 0;

        /** @var PlanPrice $price */
        $price = PlanPrice::has('plan')->with(['plan.group.group', 'plan.capacity'])->find($priceId);

        if (is_null($price)) {
            throw new ServiceException('不存在该价格方案');
        }

        $amount = $price->price;

        if (!is_null($couponCode)) {
            /** @var Coupon $coupon */
            $coupon = Coupon::where('code', $couponCode)->where('expired_at', '>', now())->first();

            if (is_null($coupon)) {
                throw new ServiceException('优惠券不存在或已到期');
            }

            // 判断优惠券使用次数
            if ($this->getCouponUsedNum($coupon) >= $coupon->usage_limit) {
                throw new ServiceException('优惠券不存在或已到期');
            }

            $amount = match ($coupon->type) {
                CouponType::Direct => $amount - $coupon->value,
                CouponType::Percent => $amount * $coupon->value,
            };

            $deductAmount = $price->price - $amount;
            $couponId = $coupon->id;
        }

        return [
            'plan_id' => $price->plan_id, // 套餐ID
            'coupon_id' => $couponId, // 优惠券ID
            'snapshot' => $price->plan,
            'product' => $price->withoutRelations(),
            'amount' => max(0, $amount), // 订单金额(分)
            'deduct_amount' => max(0, $deductAmount), // 优惠券抵扣金额(分)
        ];
    }

    /**
     * 获取优惠券使用次数
     * @param Coupon $coupon
     * @return int
     */
    public function getCouponUsedNum(Coupon $coupon): int
    {
        // 注意，订单不能被删除，否则会释放优惠券使用次数
        return Order::where('coupon_id', $coupon->id)->where('status', '<>', OrderStatus::Cancelled)->count();
    }

    /**
     * 完成订单
     *
     * @param Order $order
     * @param array $appends
     * @return bool
     * @throws Throwable
     */
    public function complete(Order $order, array $appends = []): bool
    {
        return DB::transaction(function () use ($order, $appends) {
            $order->load(['user', 'plan' => ['group', 'capacity']]);

            // 创建vip套餐，判断是否存在该角色组
            if ($order->plan->type === PlanType::Vip && $order->plan->group?->group) {
                $order->user->groups()->create([
                    'user_id' => $order->user_id,
                    'group_id' => $order->plan->group->group->id,
                    'order_id' => $order->id,
                    'from' => UserGroupFrom::Subscribe,
                    'expired_at' => now()->addMinutes((int)($order->product['duration'] ?? 0)),
                ]);
            }

            // 创建容量套餐
            if ($order->plan->type === PlanType::Storage && $order->plan->capacity) {
                $order->user->capacities()->create([
                    'user_id' => $order->user_id,
                    'capacity' => $order->plan->capacity->capacity,
                    'order_id' => $order->id,
                    'from' => UserCapacityFrom::Subscribe,
                    'expired_at' => now()->addMinutes((int)($order->product['duration'] ?? 0)),
                ]);
            }

            $order->fill($appends);
            $order->paid_at = now();
            $order->status = OrderStatus::Paid;
            return $order->save();
        });
    }

    /**
     * 取消订单
     *
     * @param Order $order
     * @return bool
     */
    public function cancel(Order $order): bool
    {
        $order->canceled_at = now();
        $order->status = OrderStatus::Cancelled;
        return $order->save();
    }

    /**
     * 设置订单金额
     *
     * @param Order $order
     * @param int|float $amount
     * @return bool
     */
    public function setAmount(Order $order, int|float $amount = 0): bool
    {
        $order->amount = $amount;
        return $order->save();
    }

    /**
     * 删除订单
     *
     * @param Order $order
     * @return bool
     */
    public function destroy(Order $order): bool
    {
        return $order->delete();
    }

    /**
     * 统一支付
     *
     * @param Order $order
     * @param PaymentProvider $platform
     * @param PaymentChannel $channel
     * @param PaymentMethod $method
     * @param array $config
     * @return array
     */
    public function pay(Order $order, PaymentProvider $platform, PaymentChannel $channel, PaymentMethod $method, array $config): array
    {
        return PaymentDriverFactory::create($platform, $config)->createOrder(new CreateOrderDto(
            outTradeNo: $order->out_trade_no,
            subject: '购买套餐',
            amount: $order->getRawOriginal('amount'),
        ), $channel, $method);
    }
}
