<?php

namespace App\Http\Resources;

use App\Models\Dish;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Dish
 */
class DishResource extends JsonResource
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
            'name' => $this->cookingMethod?->name,
            'additional_price' => $this->additional_price,
            'note' => $this->note,
        ];
    }
}
