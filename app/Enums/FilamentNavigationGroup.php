<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FilamentNavigationGroup implements HasLabel
{

    public function getLabel(): string
    {
        return match ($this) {
        };
    }
}
