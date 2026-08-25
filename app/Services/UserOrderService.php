<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ServiceException;
use App\Http\Requests\OrderPaymentRequest;
use App\Models\Driver;
use App\Models\Group;
use App\Models\Order;
use App\Models\Scopes\FilterScope;
use App\OrderStatus;
use App\PaymentChannel;
use App\PaymentMethod;
use App\PaymentProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;

class UserOrderService
{
    /**
     * 订单列表
     */
    public function paginate(array $queries = []): LengthAwarePaginator
    {
        return Auth::user()->orders()
            ->withGlobalScope('filter', new FilterScope(
                q: data_get($queries, 'q'),
                likes: ['trade_no'],
                conditions: [
                    'sort:amount:ascend' => fn(Builder $builder) => $builder->orderBy('amount'),
                    'sort:amount:descend' => fn(Builder $builder) => $builder->orderByDesc('amount'),
                    'sort:status:ascend' => fn(Builder $builder) => $builder->orderBy('status'),
                    'sort:status:descend' => fn(Builder $builder) => $builder->orderByDesc('status'),
                    'sort:created_at:ascend' => fn(Builder $builder) => $builder->orderBy('created_at'),
                    'sort:created_at:descend' => fn(Builder $builder) => $builder->orderByDesc('created_at'),
                ]
            ))
            ->with(['coupon'])
            ->paginate(data_get($queries, 'per_page', 20));
    }

    /**
     * 预览订单
     */
    public function preview(int $priceId, ?string $couponCode = null): array
    {
        return \App\Facades\OrderService::preview($priceId, $couponCode);
    }

    /**
     * 创建订单
     */
    public function store(int $priceId, ?string $couponCode = null): Order
    {
        return \App\Facades\OrderService::create($priceId, $couponCode, [
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * 订单详情
     */
    public function show(string $tradeNo): Order
    {
        /** @var Order $order */
        $order = Auth::user()->orders()
            ->where('trade_no', $tradeNo)
            ->firstOrFail();

        return $order;
    }

    /**
     * 取消订单
     */
    public function cancel(string $tradeNo): bool
    {
        /** @var Order $order */
        $order = Auth::user()->orders()
            ->where('status', OrderStatus::Unpaid)
            ->where('trade_no', $tradeNo)
            ->firstOrFail();

        return \App\Facades\OrderService::cancel($order);
    }

    /**
     * 删除订单
     */
    public function destroy(string $tradeNo): bool
    {
        /** @var Order $order */
        $order = Auth::user()->orders()
            ->where('status', OrderStatus::Cancelled)
            ->where('trade_no', $tradeNo)
            ->firstOrFail();

        return \App\Facades\OrderService::destroy($order);
    }

    /**
     * 获取订单支付配置
     *
     * @param OrderPaymentRequest $request
     * @param array $appends 附加参数
     * @return array
     * @throws ServiceException
     */
    public function pay(OrderPaymentRequest $request, array $appends = []): array
    {
        $validated = $request->validated();

        /** @var Group $group */
        $group = Context::get('group');

        /** @var Order $order */
        $order = Auth::user()
            ->orders()
            ->where('status', OrderStatus::Unpaid)
            ->where('trade_no', $request->route('trade_no'))
            ->firstOrFail();

        // 获取当前支付驱动
        /** @var Driver $payment */
        $payment = $group->paymentDrivers()
            ->where('options->provider', $validated['platform'])
            ->latest()
            ->first();

        if (is_null($payment)) {
            throw new ServiceException('未配置支付驱动，请联系管理员');
        }

        // 判断是否是银联和paypal、网银，这些支付重复订单提交会报错，需要重新生成支付订单号
        if (in_array($request->validated('channel'), [
            PaymentChannel::UniPay->value,
            PaymentChannel::Paypal->value,
            PaymentChannel::Bank->value,
        ])) {
            $order->out_trade_no = Order::generateOutTradeNo();
            $order->save();
        }

        $appends = array_merge($appends, $request->only(['return_url', 'cancel_url']));
        $appends['notify_url'] = route('payment.notify', [
            'id' => $payment->id,
            'out_trade_no' => $order->out_trade_no,
        ]);

        return \App\Facades\OrderService::pay(
            order: $order,
            platform: PaymentProvider::from($validated['platform']),
            channel: PaymentChannel::from($validated['channel']),
            method: PaymentMethod::from($validated['method']),
            config: array_merge($payment->options->getArrayCopy(), $appends)
        );
    }
}
