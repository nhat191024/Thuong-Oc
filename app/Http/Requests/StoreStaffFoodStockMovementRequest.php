<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffFoodStockMovementRequest extends FormRequest
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
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['restock', 'adjustment'])],
            'quantity' => [
                'required',
                'numeric',
                'decimal:0,3',
                'between:-999999999.999,999999999.999',
                Rule::when($this->input('type') === 'restock', ['gt:0']),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.gt' => __('Số lượng nhập thêm phải lớn hơn 0.'),
            'quantity.decimal' => __('Số lượng chỉ được có tối đa 3 chữ số thập phân.'),
        ];
    }
}
