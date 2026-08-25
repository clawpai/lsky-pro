<?php

namespace App\Models;

use App\DriverType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 储存与驱动中间表
 *
 * @property DriverType $type 驱动类型
 * @property int $storage_id 储存
 * @property int $driver_id 驱动
 * @property int $sort 排序值
 * @method static Builder<static>|StorageDriver newModelQuery()
 * @method static Builder<static>|StorageDriver newQuery()
 * @method static Builder<static>|StorageDriver query()
 * @method static Builder<static>|StorageDriver whereDriverId($value)
 * @method static Builder<static>|StorageDriver whereSort($value)
 * @method static Builder<static>|StorageDriver whereStorageId($value)
 * @method static Builder<static>|StorageDriver whereType($value)
 * @mixin \Eloquent
 */
class StorageDriver extends Pivot
{
    public $timestamps = false;

    protected $table = 'storage_driver';

    protected $fillable = [
        'type',
        'storage_id',
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
