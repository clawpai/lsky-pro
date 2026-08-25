<?php

namespace App\Http\Resources;

use App\Models\Photo;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->tags->each(fn(Tag $tag) => $tag->setVisible(['id', 'name']));
        $this->resource->covers = $this->resource->photos->transform(fn(Photo $photo) => $photo->thumbnail_url)->take(3);

        $this->resource->setVisible(['id', 'name', 'intro', 'tags', 'photo_count', 'covers', 'is_public', 'created_at']);

        return parent::toArray($request);
    }
}
