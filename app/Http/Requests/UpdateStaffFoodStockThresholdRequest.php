<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffFoodStockThresholdRequest extends FormRequest
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
            'red_threshold_kg' => [
                'required',
                'numeric',
                'decimal:0,3',
                'between:0,999999999.998',
            ],
            'yellow_threshold_kg' => [
                'required',
                'numeric',
                'decimal:0,3',
                'between:0.001,999999999.999',
                'gt:red_threshold_kg',
            ],
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
            'red_threshold_kg.between' => __('Ngưỡng đỏ phải lớn hơn hoặc bằng 0 kg.'),
            'red_threshold_kg.decimal' => __('Ngưỡng đỏ chỉ được có tối đa 3 chữ số thập phân.'),
            'yellow_threshold_kg.gt' => __('Ngưỡng vàng phải lớn hơn ngưỡng đỏ.'),
            'yellow_threshold_kg.decimal' => __('Ngưỡng vàng chỉ được có tối đa 3 chữ số thập phân.'),
        ];
    }
}
