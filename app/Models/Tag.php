<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

/**
 * 标签模型
 *
 * @property int $id
 * @property string $name 名称
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Album> $albums
 * @property-read int|null $albums_count
 * @property-read Collection<int, \App\Models\Photo> $photos
 * @property-read int|null $photos_count
 * @method static Builder<static>|Tag newModelQuery()
 * @method static Builder<static>|Tag newQuery()
 * @method static Builder<static>|Tag query()
 * @method static Builder<static>|Tag whereCreatedAt($value)
 * @method static Builder<static>|Tag whereId($value)
 * @method static Builder<static>|Tag whereName($value)
 * @method static Builder<static>|Tag whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Tag extends Model
{
    protected $table = 'tags';

    protected $fillable = [
        'name',
    ];

    /**
     * 获取分配了这个标签的所有相册
     *
     * @return MorphToMany
     */
    public function albums(): MorphToMany
    {
        return $this->morphedByMany(Album::class, 'taggable');
    }

    /**
     * 获取分配了这个标签的所有图片
     *
     * @return MorphToMany
     */
    public function photos(): MorphToMany
    {
        return $this->morphedByMany(Photo::class, 'taggable');
    }
}
