<?php

namespace App\Models;

use App\ShareType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use URL;

/**
 * 分享模型
 *
 * @property int $id
 * @property int $user_id 用户
 * @property ShareType $type 分享类型
 * @property string $slug url slug
 * @property string|null $content 分享内容
 * @property string $password 密码
 * @property int $view_count 浏览量
 * @property Carbon|null $expired_at 到期时间
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Album> $albums
 * @property-read int|null $albums_count
 * @property-read Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read Collection<int, \App\Models\Photo> $photos
 * @property-read int|null $photos_count
 * @property-read Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @property-read string $url
 * @property-read \App\Models\User $user
 * @method static Builder<static>|Share newModelQuery()
 * @method static Builder<static>|Share newQuery()
 * @method static Builder<static>|Share query()
 * @method static Builder<static>|Share whereContent($value)
 * @method static Builder<static>|Share whereCreatedAt($value)
 * @method static Builder<static>|Share whereExpiredAt($value)
 * @method static Builder<static>|Share whereId($value)
 * @method static Builder<static>|Share wherePassword($value)
 * @method static Builder<static>|Share whereSlug($value)
 * @method static Builder<static>|Share whereType($value)
 * @method static Builder<static>|Share whereUpdatedAt($value)
 * @method static Builder<static>|Share whereUserId($value)
 * @method static Builder<static>|Share whereViewCount($value)
 * @mixin Eloquent
 */
class Share extends Model
{
    protected $table = 'shares';

    protected $fillable = [
        'user_id',
        'type',
        'slug',
        'content',
        'password',
        'view_count',
        'expired_at',
    ];

    /**
     * 用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 获取分配了这个分享的所有相册
     *
     * @return MorphToMany
     */
    public function albums(): MorphToMany
    {
        return $this->morphedByMany(Album::class, 'shareable');
    }

    /**
     * 获取分配了这个分享的所有图片
     *
     * @return MorphToMany
     */
    public function photos(): MorphToMany
    {
        return $this->morphedByMany(Photo::class, 'shareable');
    }

    /**
     * 点赞记录
     *
     * @return MorphMany
     */
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
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
            'type' => ShareType::class,
            'expired_at' => 'datetime',
        ];
    }

    /**
     * 分享链接
     *
     * @return Attribute
     */
    protected function url(): Attribute
    {
        return Attribute::make(fn(): string => URL::asset('/shares/' . $this->slug));
    }
}
