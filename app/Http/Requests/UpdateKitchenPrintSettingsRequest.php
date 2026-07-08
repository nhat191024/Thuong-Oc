<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKitchenPrintSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kitchen = $this->route('kitchen');

        return $kitchen?->branch_id === $this->user()?->branch_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'auto_print' => ['required', 'boolean'],
            'printer_id' => [
                'nullable',
                'integer',
                Rule::exists('printers', 'id')
                    ->where('branch_id', $this->user()?->branch_id)
                    ->where('is_active', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'auto_print.required' => 'Vui lòng chọn chế độ tự động in.',
            'auto_print.boolean' => 'Chế độ tự động in không hợp lệ.',
            'printer_id.exists' => 'Máy in không khả dụng cho chi nhánh này.',
        ];
    }
}
