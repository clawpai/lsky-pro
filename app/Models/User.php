<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\UserStatus;
use Eloquent;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * 用户模型
 *
 * @property int $id
 * @property string $avatar 头像
 * @property string $name 姓名
 * @property string $username 用户名
 * @property string|null $phone 手机号
 * @property string|null $email 邮箱
 * @property string $password 密码
 * @property string $location 所在地
 * @property string $url 个人网站
 * @property string $company 所在公司
 * @property string $company_title 工作职位
 * @property string $tagline 个性签名
 * @property string $bio 个人简介
 * @property \ArrayObject<array-key, mixed>|null $interests 兴趣标签
 * @property \ArrayObject<array-key, mixed>|null $socials 社交账号
 * @property Carbon|null $phone_verified_at
 * @property Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property bool $is_admin 是否为管理员
 * @property \ArrayObject<array-key, mixed>|null $options 配置
 * @property string|null $login_ip 最后登录 IP
 * @property string|null $register_ip 注册 IP
 * @property string|null $country_code 国家
 * @property UserStatus $status 状态
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Album> $albums
 * @property-read int|null $albums_count
 * @property-read mixed $avatar_url
 * @property-read Collection<int, \App\Models\Report> $beReports
 * @property-read int|null $be_reports_count
 * @property-read Collection<int, \App\Models\UserCapacity> $capacities
 * @property-read int|null $capacities_count
 * @property-read \App\Models\UserCapacity|null $capacity
 * @property-read \App\Models\UserGroup|null $group
 * @property-read Collection<int, \App\Models\UserGroup> $groups
 * @property-read int|null $groups_count
 * @property-read Model|\Eloquent $likeable
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, \App\Models\OAuth> $oauth
 * @property-read int|null $oauth_count
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, \App\Models\Photo> $photos
 * @property-read int|null $photos_count
 * @property-read Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @property-read Collection<int, \App\Models\Share> $shares
 * @property-read int|null $shares_count
 * @property-read Collection<int, \App\Models\TicketReply> $ticketReplies
 * @property-read int|null $ticket_replies_count
 * @property-read Collection<int, \App\Models\Ticket> $tickets
 * @property-read int|null $tickets_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, \App\Models\Violation> $violations
 * @property-read int|null $violations_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User onlyTrashed()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereAvatar($value)
 * @method static Builder<static>|User whereBio($value)
 * @method static Builder<static>|User whereCompany($value)
 * @method static Builder<static>|User whereCompanyTitle($value)
 * @method static Builder<static>|User whereCountryCode($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereDeletedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereInterests($value)
 * @method static Builder<static>|User whereIsAdmin($value)
 * @method static Builder<static>|User whereLocation($value)
 * @method static Builder<static>|User whereLoginIp($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User whereOptions($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePhone($value)
 * @method static Builder<static>|User wherePhoneVerifiedAt($value)
 * @method static Builder<static>|User whereRegisterIp($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereSocials($value)
 * @method static Builder<static>|User whereStatus($value)
 * @method static Builder<static>|User whereTagline($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User whereUrl($value)
 * @method static Builder<static>|User whereUsername($value)
 * @method static Builder<static>|User withTrashed()
 * @method static Builder<static>|User withoutTrashed()
 * @mixin Eloquent
 */
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';

    protected $fillable = [
        'avatar',
        'name',
        'username',
        'phone',
        'email',
        'email_verified_at',
        'phone_verified_at',
        'password',
        'login_ip',
        'register_ip',
        'country_code',
        'company',
        'company_title',
        'tagline',
        'location',
        'bio',
        'url',
        'interests',
        'socials',
        'is_admin',
    ];

    protected $attributes = [
        'interests' => '[]',
        'socials' => '[]',
        'options' => '{}',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getFilamentAvatarUrl(): ?string
    {
        return filled($this->avatar) ? Storage::url($this->avatar) : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin;
    }

    protected function avatarUrl(): Attribute
    {
        return new Attribute(fn () => filled($this->avatar) ? Storage::url($this->avatar) : '');
    }

    /**
     * 相册列表
     *
     * @return HasMany
     */
    public function albums(): HasMany
    {
        return $this->hasMany(Album::class, 'user_id', 'id');
    }

    /**
     * 图片列表
     *
     * @return HasMany
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class, 'user_id', 'id');
    }

    /**
     * 分享列表
     *
     * @return HasMany
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'user_id', 'id');
    }

    /**
     * 点赞的数据
     *
     * @return MorphTo
     */
    public function likeable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'likeable_type', 'likeable_id');
    }

    /**
     * 订单列表
     *
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    /**
     * 工单列表
     *
     * @return HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id', 'id');
    }

    /**
     * 工单回复列表
     *
     * @return HasMany
     */
    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'user_id', 'id');
    }

    /**
     * 当前时间使用的组
     *
     * @return HasOne
     */
    public function group(): HasOne
    {
        return $this->groups()->one()->latestOfMany();
    }

    /**
     * 角色组列表
     *
     * @return HasMany
     */
    public function groups(): HasMany
    {
        return $this->hasMany(UserGroup::class, 'user_id', 'id');
    }

    /**
     * 当前时间使用的容量
     *
     * @return HasOne
     */
    public function capacity(): HasOne
    {
        return $this->capacities()->one()->latestOfMany();
    }

    /**
     * 容量列表
     *
     * @return HasMany
     */
    public function capacities(): HasMany
    {
        return $this->hasMany(UserCapacity::class, 'user_id', 'id');
    }

    /**
     * 违规记录
     *
     * @return HasMany
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'user_id', 'id');
    }

    /**
     * 被举报记录
     *
     * @return HasMany
     */
    public function beReports(): HasMany
    {
        return $this->hasMany(Report::class, 'report_user_id', 'id');
    }

    /**
     * 授权信息表
     *
     * @return HasMany
     */
    public function oauth(): HasMany
    {
        return $this->hasMany(OAuth::class, 'user_id', 'id');
    }

    /**
     * 举报记录
     *
     * @return MorphMany
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'interests' => AsArrayObject::class,
            'socials' => AsArrayObject::class,
            'options' => AsArrayObject::class,
            'status' => UserStatus::class,
        ];
    }
}
