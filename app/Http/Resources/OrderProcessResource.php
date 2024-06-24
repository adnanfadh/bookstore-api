<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProcessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'order_id' => $this->order_id ?? null,
            'user' => new UserResource($this->customer),
            'item' => json_decode($this->item),
            'total' => $this->total,
            'payment_method' => $this->payment_method ?? null,
            'address' => $this->address ?? null,
            'delivery_service' => $this->delivery_service ?? null,
            'is_paid' => $this->is_paid,
            'payment_proof' => $this->payment_proof ?? null,
            'status' => $this->status,
            'is_completed' => $this->is_completed,
        ];
    }
}
