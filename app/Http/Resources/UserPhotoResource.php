<?php

namespace App\Http\Resources;

use App\Models\Album;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->group?->setVisible(['id', 'name', 'intro']);
        $this->resource->storage->setVisible(['id', 'name', 'intro', 'provider']);
        $this->resource->albums->each(fn(Album $album) => $album->setVisible(['id', 'name', 'intro']));
        $this->resource->tags->transform(fn(Tag $tag) => $tag->setVisible(['id', 'name']));
        $this->resource->append('thumbnail_url', 'public_url');

        $this->resource->setVisible([
            'id', 'group', 'storage', 'albums', 'tags', 'name', 'intro', 'filename', 'pathname', 'mimetype',
            'extension', 'md5', 'sha1', 'width', 'height', 'ip_address', 'thumbnail_url', 'public_url', 'is_public',
            'expired_at', 'created_at',
        ]);

        return parent::toArray($request);
    }
}
