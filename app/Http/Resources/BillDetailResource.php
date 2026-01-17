<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $name = $this->custom_dish_name;
        $cookingMethod = null;

        if ($this->dish) {
            $name = $this->dish->food->name;
            $cookingMethod = $this->dish->cookingMethod?->name;
        }

        return [
            'id' => $this->id,
            'name' => $name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total' => $this->quantity * $this->price,
            'cooking_method' => $cookingMethod,
            'note' => $this->note,
        ];
    }
}
