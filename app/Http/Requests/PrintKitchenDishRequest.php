<?php

namespace App\Http\Requests;

use App\Models\Printer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintKitchenDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        $billDetail = $this->route('billDetail');

        $billDetail?->loadMissing('bill');

        return $billDetail?->bill?->branch_id === $this->user()?->branch_id;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'printer_id' => [
                'required',
                'integer',
                Rule::exists(Printer::class, 'id')
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
            'printer_id.required' => 'Vui lòng chọn máy in.',
            'printer_id.exists' => 'Máy in không khả dụng cho chi nhánh này.',
        ];
    }
}
