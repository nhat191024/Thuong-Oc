<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrintKitchenDishRequest extends FormRequest
{
    public function authorize(): bool
    {
        $billDetail = $this->route('billDetail');

        $billDetail?->loadMissing('bill');

        return $billDetail?->bill?->branch_id === $this->user()?->branch_id;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
