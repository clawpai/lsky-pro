<?php

namespace App\Models;

use App\DriverType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 驱动器模型
 *
 * @property int $id
 * @property DriverType $type 驱动类型
 * @property string $name 名称
 * @property string $intro 简介
 * @property \ArrayObject<array-key, mixed>|null $options 配置
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\GroupStorage|\App\Models\GroupDriver|null $pivot
 * @property-read Collection<int, \App\Models\Group> $groups
 * @property-read int|null $groups_count
 * @property-read Collection<int, \App\Models\Storage> $storages
 * @property-read int|null $storages_count
 * @method static Builder<static>|Driver newModelQuery()
 * @method static Builder<static>|Driver newQuery()
 * @method static Builder<static>|Driver onlyTrashed()
 * @method static Builder<static>|Driver query()
 * @method static Builder<static>|Driver whereCreatedAt($value)
 * @method static Builder<static>|Driver whereDeletedAt($value)
 * @method static Builder<static>|Driver whereId($value)
 * @method static Builder<static>|Driver whereIntro($value)
 * @method static Builder<static>|Driver whereName($value)
 * @method static Builder<static>|Driver whereOptions($value)
 * @method static Builder<static>|Driver whereType($value)
 * @method static Builder<static>|Driver whereUpdatedAt($value)
 * @method static Builder<static>|Driver withTrashed()
 * @method static Builder<static>|Driver withoutTrashed()
 * @mixin Eloquent
 */
class Driver extends Model
{
    use SoftDeletes;

    protected $table = 'drivers';

    protected $fillable = [
        'type',
        'name',
        'intro',
        'options',
    ];

    protected $attributes = [
        'options' => '{}'
    ];

    /**
     * 获取本地储存驱动默认配置
     *
     * @return array
     */
    public static function getLocalStorageDefaultOptions(): array
    {
        return [...self::getStorageDefaultOptions(), ...[
            'root' => storage_path('app/uploads'), // 文件储存目录
        ]];
    }

    /**
     * 获取储存驱动默认配置
     *
     * @return array<string, mixed>
     */
    public static function getStorageDefaultOptions(): array
    {
        return [
            'public_url' => config('app.url'), // 访问地址
            'naming_rule' => '{Ymd}/{md5}', // 文件命名规则
            'generate_thumbnail' => true, // 是否生成缩略图
            'thumbnail_max_size' => 800, // 缩略图最大尺寸
            'thumbnail_quality' => 90, // 缩略图质量
        ];
    }

    /**
     * 该驱动所关联的组
     *
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, GroupDriver::class, 'driver_id', 'group_id')
            ->using(GroupDriver::class);
    }

    /**
     * 该驱动所关联的储存
     *
     * @return BelongsToMany
     */
    public function storages(): BelongsToMany
    {
        return $this->belongsToMany(Storage::class, GroupStorage::class, 'driver_id', 'storage_id')
            ->using(GroupStorage::class);
    }

    protected function intro(): Attribute
    {
        return Attribute::set(fn($value): string => $value ?: '');
    }

    protected function casts(): array
    {
        return [
            'type' => DriverType::class,
            'options' => AsArrayObject::class,
        ];
    }
}
