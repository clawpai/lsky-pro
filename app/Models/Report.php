<?php

namespace App\Models;

use App\ReportStatus;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 举报模型
 *
 * @property int $id
 * @property int|null $report_user_id 被举报用户
 * @property string $reportable_type
 * @property int $reportable_id
 * @property string|null $content 原因
 * @property ReportStatus $status 状态
 * @property Carbon|null $handled_at 处理时间
 * @property string|null $ip_address IP 地址
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User|null $reportUser
 * @property-read Model|\Eloquent $reportable
 * @method static Builder<static>|Report newModelQuery()
 * @method static Builder<static>|Report newQuery()
 * @method static Builder<static>|Report onlyTrashed()
 * @method static Builder<static>|Report query()
 * @method static Builder<static>|Report whereContent($value)
 * @method static Builder<static>|Report whereCreatedAt($value)
 * @method static Builder<static>|Report whereDeletedAt($value)
 * @method static Builder<static>|Report whereHandledAt($value)
 * @method static Builder<static>|Report whereId($value)
 * @method static Builder<static>|Report whereIpAddress($value)
 * @method static Builder<static>|Report whereReportUserId($value)
 * @method static Builder<static>|Report whereReportableId($value)
 * @method static Builder<static>|Report whereReportableType($value)
 * @method static Builder<static>|Report whereStatus($value)
 * @method static Builder<static>|Report whereUpdatedAt($value)
 * @method static Builder<static>|Report withTrashed()
 * @method static Builder<static>|Report withoutTrashed()
 * @mixin Eloquent
 */
class Report extends Model
{
    use SoftDeletes;

    protected $table = 'reports';

    protected $fillable = [
        'report_user_id',
        'reportable_type',
        'reportable_id',
        'content',
        'status',
        'handled_at',
        'ip_address',
    ];

    public function casts(): array
    {
        return [
            'handled_at' => 'datetime',
            'status' => ReportStatus::class,
        ];
    }

    /**
     * 被举报用户
     *
     * @return BelongsTo
     */
    public function reportUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'report_user_id', 'id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'reportable_type', 'reportable_id');
    }
}
