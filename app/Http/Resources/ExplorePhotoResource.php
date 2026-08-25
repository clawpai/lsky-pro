<?php

namespace App\Http\Resources;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ExplorePhotoResource extends JsonResource
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

        $this->resource->tags->each(fn(Tag $tag) => $tag->setVisible(['id', 'name']));
        $this->resource->user->append('avatar_url')->setVisible(['id', 'avatar_url', 'username', 'name', 'is_admin']);
        $this->resource->append(['thumbnail_url', 'public_url'])->setVisible([
            'id', 'tags', 'user', 'name', 'intro', 'extension', 'width', 'height', 'thumbnail_url', 'public_url', 'is_liked', 'size'
        ]);

        // 当前用户是否点赞了
        $this->resource->is_liked = !is_null($user) && $this->resource->likes()->where('user_id', $user->id)->exists();

        return parent::toArray($request);
    }
}
