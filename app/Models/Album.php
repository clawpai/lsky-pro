<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 相册表
 *
 * @property int $id
 * @property int|null $user_id 用户
 * @property string $name 名称
 * @property string $intro 介绍
 * @property bool $is_public 是否公开
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read Collection<int, \App\Models\Photo> $photos
 * @property-read int|null $photos_count
 * @property-read Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @property-read Collection<int, \App\Models\Share> $shares
 * @property-read int|null $shares_count
 * @property-read Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read \App\Models\User|null $user
 * @method static Builder<static>|Album explore(array $queries = [])
 * @method static Builder<static>|Album newModelQuery()
 * @method static Builder<static>|Album newQuery()
 * @method static Builder<static>|Album onlyTrashed()
 * @method static Builder<static>|Album query()
 * @method static Builder<static>|Album whereCreatedAt($value)
 * @method static Builder<static>|Album whereDeletedAt($value)
 * @method static Builder<static>|Album whereId($value)
 * @method static Builder<static>|Album whereIntro($value)
 * @method static Builder<static>|Album whereIsPublic($value)
 * @method static Builder<static>|Album whereName($value)
 * @method static Builder<static>|Album whereUpdatedAt($value)
 * @method static Builder<static>|Album whereUserId($value)
 * @method static Builder<static>|Album withTrashed()
 * @method static Builder<static>|Album withoutTrashed()
 * @mixin Eloquent
 */
class Album extends Model
{
    use SoftDeletes;

    protected $table = 'albums';

    protected $fillable = [
        'user_id',
        'name',
        'intro',
        'is_public',
    ];

    /**
     * 获取广场的数据
     */
    public function scopeExplore(Builder $builder, array $queries = []): void
    {
        $builder->withCount('photos as photo_count')
            ->withCount('likes as like_count')
            ->with(['tags', 'user', 'photos' => fn(BelongsToMany $belongsToMany) => $belongsToMany->with('storage')->latest()->take(3)])
            ->has('user')
            ->where('is_public', true)
            ->when(!empty($queries['q']), fn(Builder $builder) => $builder->where(function (Builder $builder) use ($queries) {
                return $builder->where('name', 'like', "%{$queries['q']}%")->orWhere('intro', 'like', "%{$queries['q']}%");
            }))
            ->latest();
    }

    /**
     * 所属用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * 分享信息
     *
     * @return MorphToMany
     */
    public function shares(): MorphToMany
    {
        return $this->morphToMany(Share::class, 'shareable');
    }

    /**
     * 标签
     *
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * 图片
     *
     * @return BelongsToMany
     */
    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Photo::class, 'album_photo', 'album_id', 'photo_id');
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
            'is_public' => 'boolean',
        ];
    }

    protected function intro(): Attribute
    {
        return Attribute::set(fn($value): string => $value ?: '');
    }
}
