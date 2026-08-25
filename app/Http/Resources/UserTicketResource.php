<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->setVisible([
            'id', 'issue_no', 'title', 'level', 'status', 'created_at',
        ]);

        return parent::toArray($request);
    }
}
