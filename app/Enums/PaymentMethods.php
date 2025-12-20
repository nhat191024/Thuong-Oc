<?php

namespace App\Enums;

enum PaymentMethods: string
{
    case CASH = 'cash';
    case QR_CODE = 'qr_code';

    public function label(): string
    {
        return match ($this) {
            PaymentMethods::CASH => __('Tiền mặt'),
            PaymentMethods::QR_CODE => __('QR Code'),
        };
    }
}
