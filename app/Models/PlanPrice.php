<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 套餐价格模型
 *
 * @property int $id
 * @property int $plan_id 计划
 * @property string $name 名称
 * @property int $duration 时长(分钟)
 * @property int $price 价格(分)
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Plan $plan
 * @method static Builder<static>|PlanPrice newModelQuery()
 * @method static Builder<static>|PlanPrice newQuery()
 * @method static Builder<static>|PlanPrice query()
 * @method static Builder<static>|PlanPrice whereCreatedAt($value)
 * @method static Builder<static>|PlanPrice whereDuration($value)
 * @method static Builder<static>|PlanPrice whereId($value)
 * @method static Builder<static>|PlanPrice whereName($value)
 * @method static Builder<static>|PlanPrice wherePlanId($value)
 * @method static Builder<static>|PlanPrice wherePrice($value)
 * @method static Builder<static>|PlanPrice whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PlanPrice extends Model
{
    protected $table = 'plan_prices';

    protected $fillable = [
        'plan_id',
        'name',
        'duration',
        'price',
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

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => $value * 100,
        );
    }
}
