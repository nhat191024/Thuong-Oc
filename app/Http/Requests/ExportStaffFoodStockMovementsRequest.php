<?php

namespace App\Http\Requests;

use App\Models\FoodStockMovement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportStaffFoodStockMovementsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'type' => [
                'nullable',
                Rule::in([
                    FoodStockMovement::TYPE_RESTOCK,
                    FoodStockMovement::TYPE_USAGE,
                    FoodStockMovement::TYPE_RETURN,
                    FoodStockMovement::TYPE_ADJUSTMENT,
                ]),
            ],
        ];
    }
}
