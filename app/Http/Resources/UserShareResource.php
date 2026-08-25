<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserShareResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->setVisible([
            'id', 'type', 'slug', 'content', 'view_count', 'like_count', 'password', 'expired_at', 'created_at',
        ]);

        return parent::toArray($request);
    }
}
