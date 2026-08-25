<?php

namespace App\Http\Resources;

use App\Models\Photo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ExploreAlbumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $user */
        $user = Auth::guard('sanctum')->user();

        $this->resource->user->append('avatar_url')->setVisible(['id', 'avatar_url', 'username', 'name', 'is_admin']);
        $this->resource->tags->each(fn(Tag $tag) => $tag->setVisible(['id', 'name']));
        $this->resource->covers = $this->resource->photos->transform(fn(Photo $photo) => $photo->thumbnail_url);

        $this->resource->setVisible(['id', 'name', 'intro', 'tags', 'user', 'photo_count', 'is_liked', 'covers', 'created_at']);

        // 当前用户是否点赞了
        $this->resource->is_liked = !is_null($user) && $this->resource->likes()->where('user_id', $user->id)->exists();

        return parent::toArray($request);
    }
}
