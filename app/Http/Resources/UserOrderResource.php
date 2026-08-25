<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class UserOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->resource->snapshot = Arr::only($this->resource->snapshot, ['name', 'intro', 'features', 'badge']);
        $this->resource->product = Arr::only($this->resource->product, ['name', 'duration', 'price']);
        $this->resource->coupon?->setVisible(['name', 'code']);

        $this->resource->setVisible([
            'trade_no', 'coupon', 'amount', 'snapshot',
            'product', 'pay_method', 'deduct_amount',
            'status', 'paid_at', 'canceled_at', 'created_at'
        ]);

        return parent::toArray($request);
    }
}
