<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $table_id
 * @property int $branch_id
 * @property array $dishes
 */
class PlaceOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'table_id' => 'required|string|exists:tables,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'user_id' => 'integer|exists:users,id',
            'customer_id' => 'integer|exists:users,id',
            'dishes' => 'required|array|min:1',
            'dishes.*.dish_id' => 'nullable|integer|exists:dishes,id|required_without:dishes.*.custom_dish_name',
            'dishes.*.custom_dish_name' => 'nullable|string|max:255|required_without:dishes.*.dish_id',
            'dishes.*.custom_kitchen_id' => 'nullable|integer|exists:kitchens,id',
            'dishes.*.quantity' => 'required|integer|min:1',
            'dishes.*.price' => 'required|integer|min:0',
            'dishes.*.note' => 'nullable|string|max:255',
        ];
    }
}
