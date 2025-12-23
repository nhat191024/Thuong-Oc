<?php

namespace App\Enums;

enum TableActiveStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('Hoạt Động'),
            self::INACTIVE => __('Không Hoạt Động'),
        };
    }
}
