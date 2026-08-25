<?php

namespace App\Models;

use App\CouponType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 优惠券模型
 *
 * @property int $id
 * @property CouponType $type 折扣类型
 * @property string $name 名称
 * @property string $code 券码
 * @property string $value 金额或折扣率
 * @property int $usage_limit 可使用次数
 * @property Carbon|null $expired_at 到期时间
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @method static Builder<static>|Coupon newModelQuery()
 * @method static Builder<static>|Coupon newQuery()
 * @method static Builder<static>|Coupon onlyTrashed()
 * @method static Builder<static>|Coupon query()
 * @method static Builder<static>|Coupon whereCode($value)
 * @method static Builder<static>|Coupon whereCreatedAt($value)
 * @method static Builder<static>|Coupon whereDeletedAt($value)
 * @method static Builder<static>|Coupon whereExpiredAt($value)
 * @method static Builder<static>|Coupon whereId($value)
 * @method static Builder<static>|Coupon whereName($value)
 * @method static Builder<static>|Coupon whereType($value)
 * @method static Builder<static>|Coupon whereUpdatedAt($value)
 * @method static Builder<static>|Coupon whereUsageLimit($value)
 * @method static Builder<static>|Coupon whereValue($value)
 * @method static Builder<static>|Coupon withTrashed()
 * @method static Builder<static>|Coupon withoutTrashed()
 * @mixin Eloquent
 */
class Coupon extends Model
{
    use SoftDeletes;

    protected $table = 'coupons';

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'usage_limit',
        'expired_at',
    ];

    /**
     * 所有使用优惠券的订单
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'expired_at' => 'datetime',
        ];
    }
}
