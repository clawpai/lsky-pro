<?php

namespace App\Models;

use App\PageType;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 页面模型
 *
 * @property int $id
 * @property PageType $type 类型
 * @property string $name 名称
 * @property string $icon 图标
 * @property string $title 标题
 * @property string|null $content 网页内容
 * @property string|null $keywords 网页关键字
 * @property string|null $description 网页描述
 * @property string $slug url slug
 * @property string $url 跳转url
 * @property int $view_count 浏览量
 * @property int $sort 排序值
 * @property bool $is_show 是否显示
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Page newModelQuery()
 * @method static Builder<static>|Page newQuery()
 * @method static Builder<static>|Page query()
 * @method static Builder<static>|Page whereContent($value)
 * @method static Builder<static>|Page whereCreatedAt($value)
 * @method static Builder<static>|Page whereDescription($value)
 * @method static Builder<static>|Page whereIcon($value)
 * @method static Builder<static>|Page whereId($value)
 * @method static Builder<static>|Page whereIsShow($value)
 * @method static Builder<static>|Page whereKeywords($value)
 * @method static Builder<static>|Page whereName($value)
 * @method static Builder<static>|Page whereSlug($value)
 * @method static Builder<static>|Page whereSort($value)
 * @method static Builder<static>|Page whereTitle($value)
 * @method static Builder<static>|Page whereType($value)
 * @method static Builder<static>|Page whereUpdatedAt($value)
 * @method static Builder<static>|Page whereUrl($value)
 * @method static Builder<static>|Page whereViewCount($value)
 * @mixin Eloquent
 */
class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'type',
        'name',
        'icon',
        'title',
        'content',
        'slug',
        'url',
        'view_count',
        'sort',
        'is_show',
    ];

    protected function casts(): array
    {
        return [
            'type' => PageType::class,
            'is_show' => 'boolean',
        ];
    }
}
