<?php

namespace App\Filament\Resources\CookingMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CookingMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Tên phương pháp nấu'))
                    ->required(),
            ]);
    }
}
