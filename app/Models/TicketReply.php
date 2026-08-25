<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 工单回复记录模型
 *
 * @property int $id
 * @property int $ticket_id 工单
 * @property int $user_id 用户
 * @property string $content 内容
 * @property bool $is_notify 是否需要接收通知
 * @property Carbon|null $read_at 已读时间
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Ticket $ticket
 * @property-read \App\Models\User $user
 * @method static Builder<static>|TicketReply newModelQuery()
 * @method static Builder<static>|TicketReply newQuery()
 * @method static Builder<static>|TicketReply onlyTrashed()
 * @method static Builder<static>|TicketReply query()
 * @method static Builder<static>|TicketReply whereContent($value)
 * @method static Builder<static>|TicketReply whereCreatedAt($value)
 * @method static Builder<static>|TicketReply whereDeletedAt($value)
 * @method static Builder<static>|TicketReply whereId($value)
 * @method static Builder<static>|TicketReply whereIsNotify($value)
 * @method static Builder<static>|TicketReply whereReadAt($value)
 * @method static Builder<static>|TicketReply whereTicketId($value)
 * @method static Builder<static>|TicketReply whereUpdatedAt($value)
 * @method static Builder<static>|TicketReply whereUserId($value)
 * @method static Builder<static>|TicketReply withTrashed()
 * @method static Builder<static>|TicketReply withoutTrashed()
 * @mixin Eloquent
 */
class TicketReply extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_replies';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'content',
        'is_notify',
        'read_at',
    ];

    public function casts(): array
    {
        return [
            'is_notify' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    /**
     * 工单
     *
     * @return BelongsTo
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'id')->withTrashed();
    }

    /**
     * 用户
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed();
    }
}
