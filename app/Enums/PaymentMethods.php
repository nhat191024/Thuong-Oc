<?php

namespace App\Enums;

enum PaymentMethods: string
{
    case CASH = 'cash';
    case QR_CODE = 'qr_code';
}
