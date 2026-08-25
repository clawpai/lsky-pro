<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 
 *
 * @property int $id
 * @property int|null $tag_id 标签
 * @property int|null $user_id 用户
 * @property string $taggable_type
 * @property int $taggable_id
 * @property-read Model|\Eloquent $taggable
 * @property-read \App\Models\User|null $user
 * @method static Builder<static>|Taggable newModelQuery()
 * @method static Builder<static>|Taggable newQuery()
 * @method static Builder<static>|Taggable query()
 * @method static Builder<static>|Taggable whereId($value)
 * @method static Builder<static>|Taggable whereTagId($value)
 * @method static Builder<static>|Taggable whereTaggableId($value)
 * @method static Builder<static>|Taggable whereTaggableType($value)
 * @method static Builder<static>|Taggable whereUserId($value)
 * @mixin Eloquent
 */
class Taggable extends Model
{
    public $timestamps = false;

    protected $table = 'taggables';

    protected $fillable = [
        'tag_id',
        'user_id',
        'taggable_id',
        'taggable_type',
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
     * 标签模型
     *
     * @return MorphTo
     */
    public function taggable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'taggable_type', 'taggable_id');
    }
}
