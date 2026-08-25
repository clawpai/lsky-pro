<?php

namespace App\Models;

use App\UserGroupFrom;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 用户组模型
 *
 * @property int $id
 * @property int $user_id 用户
 * @property int $group_id 角色组
 * @property int|null $order_id 来源订单
 * @property UserGroupFrom $from 来源
 * @property Carbon|null $expired_at 到期时间
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Group $group
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\User $user
 * @method static Builder<static>|UserGroup expired()
 * @method static Builder<static>|UserGroup newModelQuery()
 * @method static Builder<static>|UserGroup newQuery()
 * @method static Builder<static>|UserGroup onlyTrashed()
 * @method static Builder<static>|UserGroup query()
 * @method static Builder<static>|UserGroup valid()
 * @method static Builder<static>|UserGroup whereCreatedAt($value)
 * @method static Builder<static>|UserGroup whereDeletedAt($value)
 * @method static Builder<static>|UserGroup whereExpiredAt($value)
 * @method static Builder<static>|UserGroup whereFrom($value)
 * @method static Builder<static>|UserGroup whereGroupId($value)
 * @method static Builder<static>|UserGroup whereId($value)
 * @method static Builder<static>|UserGroup whereOrderId($value)
 * @method static Builder<static>|UserGroup whereUpdatedAt($value)
 * @method static Builder<static>|UserGroup whereUserId($value)
 * @method static Builder<static>|UserGroup withTrashed()
 * @method static Builder<static>|UserGroup withoutTrashed()
 * @mixin Eloquent
 */
class UserGroup extends Model
{
    use SoftDeletes;

    protected $table = 'user_groups';

    protected $fillable = [
        'user_id',
        'group_id',
        'order_id',
        'from',
        'expired_at',
    ];

    /**
     * 获取有效的用户组
     *
     * @param Builder $builder
     * @return void
     */
    public function scopeValid(Builder $builder): void
    {
        // 此处必须注意排序，最早创建的用户组一定是系统赠送的，如果系统赠送的在最前面，会导致始终不会切换下一次角色组
        $builder->where('expired_at', '>', now())->orWhere->whereNull('expired_at')->orderByDesc('created_at');
    }

    /**
     * 获取到期的用户组
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
     * 角色组
     *
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
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
            'from' => UserGroupFrom::class,
            'expired_at' => 'datetime',
        ];
    }
}
