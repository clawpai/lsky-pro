<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 计划角色组模型
 *
 * @property int $id
 * @property int $plan_id 计划
 * @property int|null $group_id 角色组
 * @property-read \App\Models\Group|null $group
 * @property-read \App\Models\Plan $plan
 * @method static Builder<static>|PlanGroup newModelQuery()
 * @method static Builder<static>|PlanGroup newQuery()
 * @method static Builder<static>|PlanGroup query()
 * @method static Builder<static>|PlanGroup whereGroupId($value)
 * @method static Builder<static>|PlanGroup whereId($value)
 * @method static Builder<static>|PlanGroup wherePlanId($value)
 * @mixin Eloquent
 */
class PlanGroup extends Model
{
    public $timestamps = false;

    protected $table = 'plan_groups';

    protected $fillable = [
        'plan_id', 'group_id'
    ];

    /**
     * 套餐
     *
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
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
}
