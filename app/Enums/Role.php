<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case TABLE_ADMIN = 'table-admin';
    case STAFF = 'staff';
    case KITCHEN = 'kitchen';
    case CUSTOMER = 'customer';
}
