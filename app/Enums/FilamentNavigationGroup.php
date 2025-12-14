<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FilamentNavigationGroup implements HasLabel
{
    case FOOD;

    public function getLabel(): string
    {
        return match ($this) {
            FilamentNavigationGroup::FOOD => __('Thực phẩm'),
        };
    }
}
