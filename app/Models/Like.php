<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * 点赞模型
 *
 * @property int $id
 * @property int $user_id 用户
 * @property string $likeable_type
 * @property int $likeable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $likeable
 * @property-read \App\Models\User $user
 * @method static Builder<static>|Like newModelQuery()
 * @method static Builder<static>|Like newQuery()
 * @method static Builder<static>|Like query()
 * @method static Builder<static>|Like whereCreatedAt($value)
 * @method static Builder<static>|Like whereId($value)
 * @method static Builder<static>|Like whereLikeableId($value)
 * @method static Builder<static>|Like whereLikeableType($value)
 * @method static Builder<static>|Like whereUpdatedAt($value)
 * @method static Builder<static>|Like whereUserId($value)
 * @mixin Eloquent
 */
class Like extends Model
{
    protected $table = 'likes';

    protected $fillable = [
        'user_id',
        'likeable_type',
        'likeable_id',
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

    public function likeable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'likeable_type', 'likeable_id');
    }
}
