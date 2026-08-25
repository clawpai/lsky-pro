<?php

namespace App\Models;

use App\OrderStatus;
use App\OrderType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 订单模型
 *
 * @property int $id
 * @property int|null $plan_id 计划
 * @property int|null $user_id 用户
 * @property int|null $coupon_id 优惠券
 * @property string $trade_no 系统订单号
 * @property string $out_trade_no 支付订单号
 * @property OrderType $type 类型
 * @property int $amount 实际付款金额(分)
 * @property int $deduct_amount 抵扣金额(分)
 * @property array<array-key, mixed>|null $snapshot 产品快照
 * @property array<array-key, mixed>|null $product 购买产品数据
 * @property string $pay_method 支付方式
 * @property OrderStatus $status 状态
 * @property Carbon|null $paid_at 支付时间
 * @property Carbon|null $canceled_at 取消时间
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Coupon|null $coupon
 * @property-read \App\Models\Plan|null $plan
 * @property-read \App\Models\User|null $user
 * @method static Builder<static>|Order newModelQuery()
 * @method static Builder<static>|Order newQuery()
 * @method static Builder<static>|Order query()
 * @method static Builder<static>|Order whereAmount($value)
 * @method static Builder<static>|Order whereCanceledAt($value)
 * @method static Builder<static>|Order whereCouponId($value)
 * @method static Builder<static>|Order whereCreatedAt($value)
 * @method static Builder<static>|Order whereDeductAmount($value)
 * @method static Builder<static>|Order whereId($value)
 * @method static Builder<static>|Order whereOutTradeNo($value)
 * @method static Builder<static>|Order wherePaidAt($value)
 * @method static Builder<static>|Order wherePayMethod($value)
 * @method static Builder<static>|Order wherePlanId($value)
 * @method static Builder<static>|Order whereProduct($value)
 * @method static Builder<static>|Order whereSnapshot($value)
 * @method static Builder<static>|Order whereStatus($value)
 * @method static Builder<static>|Order whereTradeNo($value)
 * @method static Builder<static>|Order whereType($value)
 * @method static Builder<static>|Order whereUpdatedAt($value)
 * @method static Builder<static>|Order whereUserId($value)
 * @mixin Eloquent
 */
class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'plan_id',
        'user_id',
        'coupon_id',
        'trade_no',
        'type',
        'amount',
        'deduct_amount',
        'snapshot',
        'product',
        'pay_method',
        'status',
        'paid_at',
        'canceled_at',
    ];

    protected $attributes = [
        'snapshot' => '{}',
        'product' => '{}',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $order) {
            if (is_null($order->trade_no)) {
                $order->trade_no = Order::generateTradeNo();
                $order->out_trade_no = Order::generateOutTradeNo();
            }
        });
    }

    /**
     * 生成系统订单号
     */
    public static function generateTradeNo(): string
    {
        do {
            // 生成唯一订单号
            $tradeNo = date('YmdHis') . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            // 查询数据库检查是否重复
            $exists = Order::where('trade_no', $tradeNo)->exists();
        } while ($exists);

        return $tradeNo;
    }

    /**
     * 生成支付订单号
     */
    public static function generateOutTradeNo(): string
    {
        do {
            // 生成唯一订单号
            $tradeNo = date('YmdHis') . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            // 查询数据库检查是否重复
            $exists = Order::where('out_trade_no', $tradeNo)->exists();
        } while ($exists);

        return $tradeNo;
    }

    /**
     * 计划
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    /**
     * 用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 使用优惠券
     *
     * @return BelongsTo
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'snapshot' => 'array',
            'product' => 'array',
            'paid_at' => 'datetime',
            'canceled_at' => 'datetime',
        ];
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100,
        );
    }
}
