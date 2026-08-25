<?php

namespace App\Models;

use App\Facades\StorageService;
use App\PhotoStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use function Illuminate\Events\queueable;

/**
 * 图片模型
 *
 * @property int $id
 * @property int|null $user_id 用户
 * @property int|null $group_id 角色组
 * @property int|null $storage_id 储存
 * @property string $name 文件别名
 * @property string $intro 介绍
 * @property string $filename 文件原始名称
 * @property string $pathname 文件路径名称
 * @property string $mimetype 媒体类型
 * @property string $extension 文件后缀
 * @property string $md5 文件MD5
 * @property string $sha1 文件SHA1
 * @property \ArrayObject<array-key, mixed>|null $exif EXIF 信息
 * @property string $size 大小(kb)
 * @property int $width 宽度
 * @property int $height 高度
 * @property bool $is_public 是否公开
 * @property PhotoStatus $status 状态
 * @property string|null $ip_address 上传IP
 * @property Carbon|null $expired_at 到期时间
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Album> $albums
 * @property-read int|null $albums_count
 * @property-read \App\Models\Group|null $group
 * @property-read Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read string $public_url
 * @property-read Collection<int, \App\Models\Report> $reports
 * @property-read int|null $reports_count
 * @property-read array $resource_urls
 * @property-read Collection<int, \App\Models\Share> $shares
 * @property-read int|null $shares_count
 * @property-read \App\Models\Storage|null $storage
 * @property-read Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read string $thumbnail_pathname
 * @property-read string $thumbnail_url
 * @property-read \App\Models\User|null $user
 * @property-read Collection<int, \App\Models\Violation> $violations
 * @property-read int|null $violations_count
 * @method static Builder<static>|Photo day()
 * @method static Builder<static>|Photo explore(array $queries = [])
 * @method static Builder<static>|Photo hour()
 * @method static Builder<static>|Photo minute()
 * @method static Builder<static>|Photo month()
 * @method static Builder<static>|Photo newModelQuery()
 * @method static Builder<static>|Photo newQuery()
 * @method static Builder<static>|Photo onlyTrashed()
 * @method static Builder<static>|Photo query()
 * @method static Builder<static>|Photo week()
 * @method static Builder<static>|Photo whereCreatedAt($value)
 * @method static Builder<static>|Photo whereDeletedAt($value)
 * @method static Builder<static>|Photo whereExif($value)
 * @method static Builder<static>|Photo whereExpiredAt($value)
 * @method static Builder<static>|Photo whereExtension($value)
 * @method static Builder<static>|Photo whereFilename($value)
 * @method static Builder<static>|Photo whereGroupId($value)
 * @method static Builder<static>|Photo whereHeight($value)
 * @method static Builder<static>|Photo whereId($value)
 * @method static Builder<static>|Photo whereIntro($value)
 * @method static Builder<static>|Photo whereIpAddress($value)
 * @method static Builder<static>|Photo whereIsPublic($value)
 * @method static Builder<static>|Photo whereMd5($value)
 * @method static Builder<static>|Photo whereMimetype($value)
 * @method static Builder<static>|Photo whereName($value)
 * @method static Builder<static>|Photo wherePathname($value)
 * @method static Builder<static>|Photo whereSha1($value)
 * @method static Builder<static>|Photo whereSize($value)
 * @method static Builder<static>|Photo whereStatus($value)
 * @method static Builder<static>|Photo whereStorageId($value)
 * @method static Builder<static>|Photo whereUpdatedAt($value)
 * @method static Builder<static>|Photo whereUserId($value)
 * @method static Builder<static>|Photo whereWidth($value)
 * @method static Builder<static>|Photo withTrashed()
 * @method static Builder<static>|Photo withoutTrashed()
 * @mixin Eloquent
 */
class Photo extends Model
{
    use SoftDeletes;

    protected $table = 'photos';

    protected $fillable = [
        'user_id',
        'group_id',
        'storage_id',
        'name',
        'intro',
        'filename',
        'pathname',
        'mimetype',
        'extension',
        'md5',
        'sha1',
        'exif',
        'size',
        'width',
        'height',
        'is_public',
        'status',
        'ip_address',
        'expired_at',
    ];

    protected $attributes = [
        'exif' => '{}'
    ];

    protected static function booted(): void
    {
        static::deleted(queueable(function (self $photo) {
            // 图片删除后删除文件资源
            $photo->filesystem()->delete($photo->pathname);
            // 同时删除本地缩略图
            Storage::delete($photo->thumbnail_pathname);
            if ($photo->storage) {
                $photo->storage->loadMissing('processDrivers');
                /** @var Driver $processDriver */
                $processDriver = $photo->storage->processDrivers?->first();
                if (! is_null($processDriver)) {
                    // 删除云处理的缓存
                    $server = StorageService::getProcessServerFactory($photo->storage, $processDriver->options);
                    $server->deleteCache($photo->pathname);
                }
            }
        }));
    }

    /**
     * 获取此图片所在的文件系统
     *
     * @return Filesystem
     */
    public function filesystem(): Filesystem
    {
        $this->loadMissing('storage');

        return new Filesystem(StorageService::getAdapter(
            provider: $this->storage->provider,
            options: $this->storage->options,
        ), config: Arr::only($this->storage->options->getArrayCopy(), ['public_url']));
    }

    public function casts(): array
    {
        return [
            'expired_at' => 'datetime',
            'is_public' => 'boolean',
            'status' => PhotoStatus::class,
            'exif' => AsArrayObject::class,
        ];
    }

    /**
     * 查询一分钟的数据
     */
    public function scopeMinute(Builder $builder): void
    {
        $builder->where('created_at', '>=', now()->subMinute());
    }

    /**
     * 查询一小时的数据
     */
    public function scopeHour(Builder $builder): void
    {
        $builder->where('created_at', '>=', now()->subHour());
    }

    /**
     * 查询一天的数据
     */
    public function scopeDay(Builder $builder): void
    {
        $builder->where('created_at', '>=', now()->subDay());
    }

    /**
     * 查询一周的数据
     */
    public function scopeWeek(Builder $builder): void
    {
        $builder->where('created_at', '>=', now()->subWeek());
    }

    /**
     * 查询一个月的数据
     */
    public function scopeMonth(Builder $builder): void
    {
        $builder->where('created_at', '>=', now()->subMonth());
    }

    /**
     * 查询广场中的数据
     */
    public function scopeExplore(Builder $builder, array $queries = []): void
    {
        $builder->withCount('likes as like_count')
            ->with(['tags', 'user', 'storage'])
            ->has('user')
            ->where('is_public', true)
            ->where('status', PhotoStatus::Normal)
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
     * 所属组
     *
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    /**
     * 所在所有相册
     *
     * @return BelongsToMany
     */
    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class, 'album_photo', 'photo_id', 'album_id');
    }

    /**
     * 所属储存
     *
     * @return BelongsTo
     */
    public function storage(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Storage::class, 'storage_id', 'id')->withTrashed();
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
     * 分享记录
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
     * 违规记录
     *
     * @return HasMany
     */
    public function violations(): HasMany
    {
        return $this->hasMany(Violation::class, 'photo_id', 'id');
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

    protected function intro(): Attribute
    {
        return Attribute::set(fn($value): string => $value ?: '');
    }

    /**
     * 获取缩略图 url
     *
     * @return Attribute
     */
    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(function (): string {
            $filesystem = $this->thumbnailFilesystem();
            return $filesystem->exists($this->thumbnail_pathname) ?
                // 如果使用 public_url，需要保证 storage 配置中存在 public_url 配置项
                $filesystem->url($this->thumbnail_pathname) :
                $this->public_url;
        });
    }

    /**
     * 获取缩略图存放文件系统
     *
     * @return FilesystemAdapter
     */
    public function thumbnailFilesystem(): FilesystemAdapter
    {
        // 使用系统默认磁盘 public
        return Storage::disk('public');
    }

    /**
     * 获取公开 url
     *
     * @return Attribute
     */
    protected function publicUrl(): Attribute
    {
        $pathname = $this->getPublicUrlPrefix().trim($this->pathname, '/');
        return Attribute::make(fn(): string => $this->filesystem()->publicUrl($pathname));
    }

    protected function getPublicUrlPrefix(): string
    {
        return trim($this->storage?->prefix ?: '', '/') . '/';
    }

    /**
     * 获取缩略图存放路径
     *
     * @return Attribute
     */
    protected function thumbnailPathname(): Attribute
    {
        return Attribute::make(fn(): string => "thumbnails/{$this->pathname}");
    }

    /**
     * 获取 urls
     *
     * @return Attribute
     */
    protected function resourceUrls(): Attribute
    {
        return Attribute::make(function (): array {
            return [
                'url' => $this->public_url,
                'html' => "<img src=\"{$this->public_url}\" alt=\"{$this->name}\" title=\"{$this->name}\" />",
                'bbcode' => "[img]{$this->public_url}[/img]",
                'markdown' => "![{$this->name}]({$this->public_url})",
                'markdown_with_link' => "[![{$this->name}]({$this->public_url})]({$this->public_url})",
                'thumbnail_url' => $this->thumbnail_url,
            ];
        })->shouldCache();
    }
}
