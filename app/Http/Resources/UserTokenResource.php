<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->setVisible([
            'id', 'name', 'last_used_at', 'expires_at', 'created_at', 'abilities',
        ]);

        return parent::toArray($request);
    }
}
