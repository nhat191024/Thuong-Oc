<?php

namespace App\Enums;

enum BillDetailStatus: string
{
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            BillDetailStatus::APPROVED => __('Đã duyệt'),
            BillDetailStatus::CANCELLED => __('Đã hủy'),
        };
    }
}
