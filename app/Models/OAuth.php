<?php

namespace App\Models;

use App\DriverType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 三方授权表
 *
 * @property int $id
 * @property int $driver_id 三方授权驱动ID
 * @property int $user_id 用户ID
 * @property string $openid 三方授权ID
 * @property string $avatar 三方授权头像
 * @property string $email 三方授权邮箱
 * @property string $name 三方授权名称
 * @property string $nickname 三方授权昵称
 * @property \ArrayObject<array-key, mixed>|null $raw 三方授权原始信息
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Driver $driver
 * @property-read \App\Models\User $user
 * @method static Builder<static>|OAuth newModelQuery()
 * @method static Builder<static>|OAuth newQuery()
 * @method static Builder<static>|OAuth query()
 * @method static Builder<static>|OAuth whereAvatar($value)
 * @method static Builder<static>|OAuth whereCreatedAt($value)
 * @method static Builder<static>|OAuth whereDriverId($value)
 * @method static Builder<static>|OAuth whereEmail($value)
 * @method static Builder<static>|OAuth whereId($value)
 * @method static Builder<static>|OAuth whereName($value)
 * @method static Builder<static>|OAuth whereNickname($value)
 * @method static Builder<static>|OAuth whereOpenid($value)
 * @method static Builder<static>|OAuth whereRaw($value)
 * @method static Builder<static>|OAuth whereUpdatedAt($value)
 * @method static Builder<static>|OAuth whereUserId($value)
 * @mixin Eloquent
 */
class OAuth extends Model
{
    protected $table = 'oauth';

    protected $fillable = [
        'id',
        'driver_id',
        'user_id',
        'openid',
        'avatar',
        'email',
        'name',
        'nickname',
        'raw',
    ];

    protected $attributes = [
        'raw' => '{}'
    ];

    /**
     * 驱动
     *
     * @return BelongsTo
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id')->where('type', DriverType::Socialite);
    }

    /**
     * 用户信息
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'raw' => AsArrayObject::class,
        ];
    }
}
