<?php

namespace App\Enums;

enum PayStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            PayStatus::PAID => __('Đã thanh toán'),
            PayStatus::UNPAID => __('Chưa thanh toán'),
            PayStatus::CANCELLED => __('Đã huỷ'),
        };
    }
}
