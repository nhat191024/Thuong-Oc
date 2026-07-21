<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReduceStaffBillDetailQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'reductions' => ['required', 'array', 'min:1'],
            'reductions.*.bill_detail_ids' => ['required', 'array', 'min:1'],
            'reductions.*.bill_detail_ids.*' => ['required', 'integer', 'distinct'],
            'reductions.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reductions.required' => 'Không tìm thấy món cần giảm.',
            'reductions.min' => 'Không tìm thấy món cần giảm.',
            'reductions.*.bill_detail_ids.*.distinct' => 'Danh sách món cần giảm không hợp lệ.',
        ];
    }
}
