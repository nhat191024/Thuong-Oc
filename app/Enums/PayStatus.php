<?php

namespace App\Enums;

enum PayStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case CANCELLED = 'cancelled';
}
