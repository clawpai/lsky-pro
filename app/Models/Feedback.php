<?php

namespace App\Models;

use App\FeedbackType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 意见与反馈模型
 *
 * @property int $id
 * @property FeedbackType $type 类型
 * @property string $title 标题
 * @property string $name 姓名
 * @property string $email email
 * @property string $content 内容
 * @property string|null $ip_address IP 地址
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Feedback newModelQuery()
 * @method static Builder<static>|Feedback newQuery()
 * @method static Builder<static>|Feedback query()
 * @method static Builder<static>|Feedback whereContent($value)
 * @method static Builder<static>|Feedback whereCreatedAt($value)
 * @method static Builder<static>|Feedback whereEmail($value)
 * @method static Builder<static>|Feedback whereId($value)
 * @method static Builder<static>|Feedback whereIpAddress($value)
 * @method static Builder<static>|Feedback whereName($value)
 * @method static Builder<static>|Feedback whereTitle($value)
 * @method static Builder<static>|Feedback whereType($value)
 * @method static Builder<static>|Feedback whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'type',
        'title',
        'name',
        'email',
        'content',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'type' => FeedbackType::class,
        ];
    }
}
