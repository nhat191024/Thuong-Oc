<?php

namespace App\Http\Resources;

use App\Models\Food;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Food
 */
class FoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_favorite' => $this->is_favorite,
            'is_discounted' => $this->discount_price > 0,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'note' => $this->note,
            'image' => $this->getFirstMediaUrl('default', 'preview'),
            'sold_count' => $this->sold_count,
            'dishes' => DishResource::collection($this->whenLoaded('dishes')),
        ];
    }
}
