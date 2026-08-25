<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 相册和图片中间表
 *
 * @property int $album_id 相册
 * @property int $photo_id 图片
 * @property int $sort 排序值
 * @method static Builder<static>|AlbumPhoto newModelQuery()
 * @method static Builder<static>|AlbumPhoto newQuery()
 * @method static Builder<static>|AlbumPhoto query()
 * @method static Builder<static>|AlbumPhoto whereAlbumId($value)
 * @method static Builder<static>|AlbumPhoto wherePhotoId($value)
 * @method static Builder<static>|AlbumPhoto whereSort($value)
 * @mixin Eloquent
 */
class AlbumPhoto extends Model
{
    public $timestamps = false;

    protected $table = 'album_photo';

    protected $fillable = [
        'album_id',
        'photo_id',
        'sort',
    ];
}
