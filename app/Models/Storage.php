<?php

namespace App\Models;

use App\DriverType;
use App\StorageProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 储存表
 *
 * @property int $id
 * @property string $name 名称
 * @property string $intro 描述
 * @property string $prefix 储存前缀
 * @property StorageProvider $provider 储存提供者
 * @property \ArrayObject<array-key, mixed>|null $options 储存配置
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\StorageDriver|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $drivers
 * @property-read int|null $drivers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $handleDrivers
 * @property-read int|null $handle_drivers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $processDrivers
 * @property-read int|null $process_drivers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Driver> $scanDrivers
 * @property-read int|null $scan_drivers_count
 * @method static Builder<static>|Storage newModelQuery()
 * @method static Builder<static>|Storage newQuery()
 * @method static Builder<static>|Storage onlyTrashed()
 * @method static Builder<static>|Storage query()
 * @method static Builder<static>|Storage whereCreatedAt($value)
 * @method static Builder<static>|Storage whereDeletedAt($value)
 * @method static Builder<static>|Storage whereId($value)
 * @method static Builder<static>|Storage whereIntro($value)
 * @method static Builder<static>|Storage whereName($value)
 * @method static Builder<static>|Storage whereOptions($value)
 * @method static Builder<static>|Storage wherePrefix($value)
 * @method static Builder<static>|Storage whereProvider($value)
 * @method static Builder<static>|Storage whereUpdatedAt($value)
 * @method static Builder<static>|Storage withTrashed()
 * @method static Builder<static>|Storage withoutTrashed()
 * @mixin \Eloquent
 */
class Storage extends Model
{
    use SoftDeletes;

    protected $table = 'storages';

    protected $fillable = [
        'name',
        'intro',
        'prefix',
        'provider',
        'options',
    ];

    protected $attributes = [
        'options' => '{}'
    ];

    protected function casts(): array
    {
        return [
            'provider' => StorageProvider::class,
            'options' => AsArrayObject::class,
        ];
    }

    /**
     * 云处理驱动
     *
     * @return BelongsToMany
     */
    public function processDrivers(): BelongsToMany
    {
        return $this->getDriverBelongsToMany(DriverType::Process);
    }

    /**
     * 图片处理
     *
     * @return BelongsToMany
     */
    public function handleDrivers(): BelongsToMany
    {
        return $this->getDriverBelongsToMany(DriverType::Handle);
    }

    /**
     * 图片审核
     *
     * @return BelongsToMany
     */
    public function scanDrivers(): BelongsToMany
    {
        return $this->getDriverBelongsToMany(DriverType::Scan);
    }

    /**
     * 所有类型驱动器
     *
     * @return BelongsToMany
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, StorageDriver::class, 'storage_id', 'driver_id')
            ->using(StorageDriver::class);
    }

    /**
     * 获取指定类型的驱动器
     *
     * @param DriverType $driverType
     * @return BelongsToMany
     */
    protected function getDriverBelongsToMany(DriverType $driverType): BelongsToMany
    {
        return $this->drivers()->withPivotValue('type', $driverType);
    }
}
