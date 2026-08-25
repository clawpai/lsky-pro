<?php

namespace App\Models;

use App\PlanType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 套餐模型
 *
 * @property int $id
 * @property PlanType $type 类型
 * @property string $name 名称
 * @property string|null $intro 简介
 * @property \Illuminate\Support\Collection|null $features 特点
 * @property string $badge 徽章内容
 * @property int $sort 排序值
 * @property bool $is_up 是否上架
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\PlanCapacity> $capacities
 * @property-read int|null $capacities_count
 * @property-read \App\Models\PlanCapacity|null $capacity
 * @property-read \App\Models\PlanGroup|null $group
 * @property-read Collection<int, \App\Models\PlanGroup> $groups
 * @property-read int|null $groups_count
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, \App\Models\PlanPrice> $prices
 * @property-read int|null $prices_count
 * @method static Builder<static>|Plan newModelQuery()
 * @method static Builder<static>|Plan newQuery()
 * @method static Builder<static>|Plan onlyTrashed()
 * @method static Builder<static>|Plan query()
 * @method static Builder<static>|Plan whereBadge($value)
 * @method static Builder<static>|Plan whereCreatedAt($value)
 * @method static Builder<static>|Plan whereDeletedAt($value)
 * @method static Builder<static>|Plan whereFeatures($value)
 * @method static Builder<static>|Plan whereId($value)
 * @method static Builder<static>|Plan whereIntro($value)
 * @method static Builder<static>|Plan whereIsUp($value)
 * @method static Builder<static>|Plan whereName($value)
 * @method static Builder<static>|Plan whereSort($value)
 * @method static Builder<static>|Plan whereType($value)
 * @method static Builder<static>|Plan whereUpdatedAt($value)
 * @method static Builder<static>|Plan withTrashed()
 * @method static Builder<static>|Plan withoutTrashed()
 * @mixin Eloquent
 */
class Plan extends Model
{
    use SoftDeletes;

    protected $table = 'plans';

    protected $fillable = [
        'type',
        'name',
        'intro',
        'features',
        'badge',
        'sort',
        'is_up',
    ];

    /**
     * 阶梯价格
     *
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(PlanPrice::class, 'plan_id', 'id');
    }

    /**
     * 角色组
     *
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->groups()->one();
    }

    /**
     * 所有角色组
     *
     * @return HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(PlanGroup::class, 'plan_id', 'id');
    }

    /**
     * 容量
     *
     * @return HasOne
     */
    public function capacity(): HasOne
    {
        return $this->capacities()->one();
    }

    /**
     * 所有容量
     *
     * @return HasMany
     */
    public function capacities(): HasMany
    {
        return $this->hasMany(PlanCapacity::class, 'plan_id', 'id');
    }

    /**
     * 订单
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'plan_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'type' => PlanType::class,
            'features' => AsCollection::class,
            'is_up' => 'boolean',
        ];
    }
}
