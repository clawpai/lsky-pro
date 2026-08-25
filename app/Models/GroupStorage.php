<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * 组与储存中间表
 *
 * @property int $group_id 角色组
 * @property int $storage_id 储存
 * @property int $sort 排序值
 * @method static Builder<static>|GroupStorage newModelQuery()
 * @method static Builder<static>|GroupStorage newQuery()
 * @method static Builder<static>|GroupStorage query()
 * @method static Builder<static>|GroupStorage whereGroupId($value)
 * @method static Builder<static>|GroupStorage whereSort($value)
 * @method static Builder<static>|GroupStorage whereStorageId($value)
 * @mixin \Eloquent
 */
class GroupStorage extends Pivot
{
    public $timestamps = false;

    protected $table = 'group_storage';

    protected $fillable = [
        'group_id',
        'storage_id',
        'sort',
    ];
}
