<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 分享模型
 *
 * @property int $id
 * @property int $share_id 分享
 * @property string $shareable_type
 * @property int $shareable_id
 * @property-read Collection<int, \App\Models\Like> $likes
 * @property-read int|null $likes_count
 * @property-read \App\Models\Share $share
 * @property-read Model|\Eloquent $shareable
 * @property-read \App\Models\User|null $user
 * @method static Builder<static>|Shareable newModelQuery()
 * @method static Builder<static>|Shareable newQuery()
 * @method static Builder<static>|Shareable query()
 * @method static Builder<static>|Shareable whereId($value)
 * @method static Builder<static>|Shareable whereShareId($value)
 * @method static Builder<static>|Shareable whereShareableId($value)
 * @method static Builder<static>|Shareable whereShareableType($value)
 * @mixin Eloquent
 */
class Shareable extends Model
{
    public $timestamps = false;

    protected $table = 'shareables';

    protected $fillable = [
        'share_id',
        'shareable_id',
        'shareable_type',
    ];

    /**
     * 分享
     *
     * @return BelongsTo
     */
    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class, 'share_id', 'id');
    }

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
     * 分享模型
     *
     * @return MorphTo
     */
    public function shareable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'shareable_type', 'shareable_id');
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
}
