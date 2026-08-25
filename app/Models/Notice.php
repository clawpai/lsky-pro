<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * 公告模型
 *
 * @property int $id
 * @property string $title 标题
 * @property string|null $content 内容
 * @property int $view_count 阅读量
 * @property int $sort 排序值
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Notice newModelQuery()
 * @method static Builder<static>|Notice newQuery()
 * @method static Builder<static>|Notice onlyTrashed()
 * @method static Builder<static>|Notice query()
 * @method static Builder<static>|Notice whereContent($value)
 * @method static Builder<static>|Notice whereCreatedAt($value)
 * @method static Builder<static>|Notice whereDeletedAt($value)
 * @method static Builder<static>|Notice whereId($value)
 * @method static Builder<static>|Notice whereSort($value)
 * @method static Builder<static>|Notice whereTitle($value)
 * @method static Builder<static>|Notice whereUpdatedAt($value)
 * @method static Builder<static>|Notice whereViewCount($value)
 * @method static Builder<static>|Notice withTrashed()
 * @method static Builder<static>|Notice withoutTrashed()
 * @mixin Eloquent
 */
class Notice extends Model
{
    use SoftDeletes;

    protected $table = 'notices';

    protected $fillable = [
        'title',
        'content',
        'view_count',
        'sort',
    ];
}
