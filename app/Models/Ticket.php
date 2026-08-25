<?php

namespace App\Models;

use App\TicketLevel;
use App\TicketStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 工单模型
 *
 * @property int $id
 * @property int $user_id 用户
 * @property string $issue_no 工单编号
 * @property string $title 标题
 * @property TicketLevel $level 级别
 * @property TicketStatus $status 状态
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\TicketReply> $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\TicketReply|null $reply
 * @property-read \App\Models\User $user
 * @method static Builder<static>|Ticket newModelQuery()
 * @method static Builder<static>|Ticket newQuery()
 * @method static Builder<static>|Ticket onlyTrashed()
 * @method static Builder<static>|Ticket query()
 * @method static Builder<static>|Ticket whereCreatedAt($value)
 * @method static Builder<static>|Ticket whereDeletedAt($value)
 * @method static Builder<static>|Ticket whereId($value)
 * @method static Builder<static>|Ticket whereIssueNo($value)
 * @method static Builder<static>|Ticket whereLevel($value)
 * @method static Builder<static>|Ticket whereStatus($value)
 * @method static Builder<static>|Ticket whereTitle($value)
 * @method static Builder<static>|Ticket whereUpdatedAt($value)
 * @method static Builder<static>|Ticket whereUserId($value)
 * @method static Builder<static>|Ticket withTrashed()
 * @method static Builder<static>|Ticket withoutTrashed()
 * @mixin Eloquent
 */
class Ticket extends Model
{
    use SoftDeletes;

    protected $table = 'tickets';

    protected $fillable = [
        'user_id',
        'issue_no',
        'title',
        'level',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $ticket) {
            if (is_null($ticket->issue_no)) {
                $ticket->issue_no = Ticket::generateIssueNo();
            }
        });
    }

    /**
     * 生成工单号
     */
    public static function generateIssueNo(): string
    {
        $issueNo = date('YmdHis') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

        if (Ticket::where('issue_no', $issueNo)->exists()) {
            return self::generateIssueNo();
        }

        return $issueNo;
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
     * 最新一条回复
     *
     * @return HasOne
     */
    public function reply(): HasOne
    {
        return $this->replies()->one()->latestOfMany();
    }

    /**
     * 回复列表
     *
     * @return HasMany
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'ticket_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'level' => TicketLevel::class,
            'status' => TicketStatus::class,
        ];
    }
}
