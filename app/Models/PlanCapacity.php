<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 计划容量模型
 *
 * @property int $id
 * @property int $plan_id 计划
 * @property string|null $capacity 容量(kb)
 * @property-read \App\Models\Plan $plan
 * @method static Builder<static>|PlanCapacity newModelQuery()
 * @method static Builder<static>|PlanCapacity newQuery()
 * @method static Builder<static>|PlanCapacity query()
 * @method static Builder<static>|PlanCapacity whereCapacity($value)
 * @method static Builder<static>|PlanCapacity whereId($value)
 * @method static Builder<static>|PlanCapacity wherePlanId($value)
 * @mixin Eloquent
 */
class PlanCapacity extends Model
{
    public $timestamps = false;

    protected $table = 'plan_capacities';

    protected $fillable = [
        'plan_id', 'capacity'
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
}
