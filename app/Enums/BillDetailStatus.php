<?php

namespace App\Enums;

enum BillDetailStatus: string
{
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';
}
