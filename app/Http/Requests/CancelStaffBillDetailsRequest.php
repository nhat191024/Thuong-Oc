<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelStaffBillDetailsRequest extends FormRequest
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
            'bill_detail_ids' => ['required', 'array', 'min:1'],
            'bill_detail_ids.*' => ['required', 'integer', 'distinct'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bill_detail_ids.required' => 'Không tìm thấy món cần hủy.',
            'bill_detail_ids.min' => 'Không tìm thấy món cần hủy.',
            'bill_detail_ids.*.distinct' => 'Danh sách món cần hủy không hợp lệ.',
        ];
    }
}
