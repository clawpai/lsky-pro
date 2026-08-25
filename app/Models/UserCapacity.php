<?php

namespace App\Models;

use App\UserCapacityFrom;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 用户容量模型
 *
 * @property int $id
 * @property int $user_id 用户
 * @property int|null $order_id 来源订单
 * @property string|null $capacity 容量(kb)
 * @property UserCapacityFrom $from 来源
 * @property Carbon|null $expired_at 到期时间
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User $user
 * @method static Builder<static>|UserCapacity expired()
 * @method static Builder<static>|UserCapacity newModelQuery()
 * @method static Builder<static>|UserCapacity newQuery()
 * @method static Builder<static>|UserCapacity onlyTrashed()
 * @method static Builder<static>|UserCapacity query()
 * @method static Builder<static>|UserCapacity valid()
 * @method static Builder<static>|UserCapacity whereCapacity($value)
 * @method static Builder<static>|UserCapacity whereCreatedAt($value)
 * @method static Builder<static>|UserCapacity whereDeletedAt($value)
 * @method static Builder<static>|UserCapacity whereExpiredAt($value)
 * @method static Builder<static>|UserCapacity whereFrom($value)
 * @method static Builder<static>|UserCapacity whereId($value)
 * @method static Builder<static>|UserCapacity whereOrderId($value)
 * @method static Builder<static>|UserCapacity whereUpdatedAt($value)
 * @method static Builder<static>|UserCapacity whereUserId($value)
 * @method static Builder<static>|UserCapacity withTrashed()
 * @method static Builder<static>|UserCapacity withoutTrashed()
 * @mixin Eloquent
 */
class UserCapacity extends Model
{
    use SoftDeletes;

    protected $table = 'user_capacities';

    protected $fillable = [
        'user_id',
        'order_id',
        'from',
        'capacity',
        'expired_at',
    ];

    /**
     * 获取有效的用户容量
     *
     * @param Builder $builder
     * @return void
     */
    public function scopeValid(Builder $builder): void
    {
        $builder->where('expired_at', '>', now())->orWhere->whereNull('expired_at');
    }

    /**
     * 获取到期的用户容量
     *
     * @param Builder $builder
     * @return void
     */
    public function scopeExpired(Builder $builder): void
    {
        $builder->where('expired_at', '<=', now())->whereNotNull('expired_at');
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
     * 订单
     *
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'from' => UserCapacityFrom::class,
            'expired_at' => 'datetime',
        ];
    }
}
