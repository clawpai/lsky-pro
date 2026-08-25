<?php

namespace App\Models;

use App\ViolationStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 违规记录模型
 *
 * @property int $id
 * @property int|null $user_id 用户
 * @property int|null $photo_id 图片
 * @property string $reason
 * @property ViolationStatus $status 状态
 * @property Carbon|null $handled_at 处理时间
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Photo|null $photo
 * @property-read \App\Models\User|null $user
 * @method static Builder<static>|Violation newModelQuery()
 * @method static Builder<static>|Violation newQuery()
 * @method static Builder<static>|Violation onlyTrashed()
 * @method static Builder<static>|Violation query()
 * @method static Builder<static>|Violation whereCreatedAt($value)
 * @method static Builder<static>|Violation whereDeletedAt($value)
 * @method static Builder<static>|Violation whereHandledAt($value)
 * @method static Builder<static>|Violation whereId($value)
 * @method static Builder<static>|Violation wherePhotoId($value)
 * @method static Builder<static>|Violation whereReason($value)
 * @method static Builder<static>|Violation whereStatus($value)
 * @method static Builder<static>|Violation whereUpdatedAt($value)
 * @method static Builder<static>|Violation whereUserId($value)
 * @method static Builder<static>|Violation withTrashed()
 * @method static Builder<static>|Violation withoutTrashed()
 * @mixin Eloquent
 */
class Violation extends Model
{
    use SoftDeletes;

    protected $table = 'violations';

    protected $fillable = [
        'user_id',
        'photo_id',
        'reason',
        'status',
        'handled_at',
    ];

    public function casts(): array
    {
        return [
            'handled_at' => 'datetime',
            'status' => ViolationStatus::class,
        ];
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
     * 图片
     *
     * @return BelongsTo
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class, 'photo_id', 'id');
    }
}
