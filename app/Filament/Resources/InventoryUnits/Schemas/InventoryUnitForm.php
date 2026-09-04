<?php

namespace App\Filament\Resources\InventoryUnits\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InventoryUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Tên đơn vị'))
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('symbol')
                    ->label(__('Ký hiệu'))
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
