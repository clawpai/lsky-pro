<?php

namespace App\Models;

use App\DriverType;
use App\Facades\AppService;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 角色组模型
 *
 * @property int $id
 * @property string $name 名称
 * @property string $intro 描述
 * @property \ArrayObject<array-key, mixed>|null $options 配置
 * @property bool $is_default 是否为默认组
 * @property bool $is_guest 是否为游客组
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\GroupStorage|\App\Models\GroupDriver|null $pivot
 * @property-read Collection<int, \App\Models\Driver> $drivers
 * @property-read int|null $drivers_count
 * @property-read Collection<int, \App\Models\Driver> $mailDrivers
 * @property-read int|null $mail_drivers_count
 * @property-read Collection<int, \App\Models\Driver> $paymentDrivers
 * @property-read int|null $payment_drivers_count
 * @property-read Collection<int, \App\Models\Driver> $smsDrivers
 * @property-read int|null $sms_drivers_count
 * @property-read Collection<int, \App\Models\Storage> $storages
 * @property-read int|null $storages_count
 * @property-read Collection<int, \App\Models\UserGroup> $userGroups
 * @property-read int|null $user_groups_count
 * @method static Builder<static>|Group newModelQuery()
 * @method static Builder<static>|Group newQuery()
 * @method static Builder<static>|Group onlyTrashed()
 * @method static Builder<static>|Group query()
 * @method static Builder<static>|Group whereCreatedAt($value)
 * @method static Builder<static>|Group whereDeletedAt($value)
 * @method static Builder<static>|Group whereId($value)
 * @method static Builder<static>|Group whereIntro($value)
 * @method static Builder<static>|Group whereIsDefault($value)
 * @method static Builder<static>|Group whereIsGuest($value)
 * @method static Builder<static>|Group whereName($value)
 * @method static Builder<static>|Group whereOptions($value)
 * @method static Builder<static>|Group whereUpdatedAt($value)
 * @method static Builder<static>|Group withTrashed()
 * @method static Builder<static>|Group withoutTrashed()
 * @mixin Eloquent
 */
class Group extends Model
{
    use SoftDeletes;

    protected $table = 'groups';

    protected $fillable = [
        'name',
        'intro',
        'options',
        'is_default',
        'is_guest',
    ];

    protected $attributes = [
        'options' => '{}'
    ];

    /**
     * 组默认配置值
     *
     * @return array<string, mixed>
     */
    public static function getDefaultOptions(): array
    {
        return [
            'max_upload_size' => 5120, // 最大上传文件大小，默认 5MB
            'file_expire_seconds' => 0, // 文件过期时间(秒)
            'limit_concurrent_upload' => 4, // 并发上传数量限制
            'limit_per_minute' => 20, // 每分钟上传数量限制
            'limit_per_hour' => 100, // 每小时上传数量限制
            'limit_per_day' => 300, // 每天上传数量限制
            'limit_per_week' => 600, // 每周上传数量限制
            'limit_per_month' => 1000, // 每月上传数量限制
            'allow_file_types' => AppService::getAllSupportedImageTypes(), // 允许上传的文件类型
        ];
    }

    /**
     * 储存驱动
     *
     * @return BelongsToMany
     */
    public function storages(): BelongsToMany
    {
        return $this->belongsToMany(Storage::class, GroupStorage::class, 'group_id', 'storage_id')
            ->using(GroupStorage::class);
    }

    /**
     * 短信驱动
     *
     * @return BelongsToMany
     */
    public function smsDrivers(): BelongsToMany
    {
        return $this->getDriverBelongsToMany(DriverType::Sms);
    }

    /**
     * 邮件驱动
     *
     * @return BelongsToMany
     */
    public function mailDrivers(): BelongsToMany
    {
        return $this->getDriverBelongsToMany(DriverType::Mail);
    }

    /**
     * 支付驱动
     *
     * @return BelongsToMany
     */
    public function paymentDrivers(): BelongsToMany
    {
        return $this->getDriverBelongsToMany(DriverType::Payment);
    }

    /**
     * 所有类型驱动器
     *
     * @return BelongsToMany
     */
    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, GroupDriver::class, 'group_id', 'driver_id')
            ->using(GroupDriver::class);
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

    /**
     * 用户所关联的组
     * 
     * @return HasMany
     */
    public function userGroups(): HasMany
    {
        return $this->hasMany(UserGroup::class, 'group_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'options' => AsArrayObject::class,
            'is_default' => 'boolean',
            'is_guest' => 'boolean',
        ];
    }
}
