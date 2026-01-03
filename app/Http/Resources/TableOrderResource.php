<?php

namespace App\Http\Resources;

use App\Models\BillDetail;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BillDetail
 */
class TableOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'foodId' => $this->dish->food->id,
            'dishId' => $this->dish_id,
            'name' => $this->dish->food->name,
            'quantity' => $this->quantity,
            'cookingMethod' => $this->dish->cookingMethod?->name,
            'price' => $this->price,
            'cookingMethodId' => $this->dish->cooking_method_id,
            'note' => $this->note,
        ];
    }
}
