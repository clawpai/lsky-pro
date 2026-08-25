<?php

namespace App\Models;

use App\DriverType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 角色组驱动表
 *
 * @property DriverType $type 驱动类型
 * @property int $group_id 角色组
 * @property int $driver_id 驱动
 * @property int $sort 排序值
 * @method static Builder<static>|GroupDriver newModelQuery()
 * @method static Builder<static>|GroupDriver newQuery()
 * @method static Builder<static>|GroupDriver query()
 * @method static Builder<static>|GroupDriver whereDriverId($value)
 * @method static Builder<static>|GroupDriver whereGroupId($value)
 * @method static Builder<static>|GroupDriver whereSort($value)
 * @method static Builder<static>|GroupDriver whereType($value)
 * @mixin Eloquent
 */
class GroupDriver extends Pivot
{
    public $timestamps = false;

    protected $table = 'group_driver';

    protected $fillable = [
        'type',
        'group_id',
        'driver_id',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'type' => DriverType::class,
        ];
    }
}
