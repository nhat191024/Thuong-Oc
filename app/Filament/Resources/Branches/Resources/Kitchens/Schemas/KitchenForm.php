<?php

namespace App\Filament\Resources\Branches\Resources\Kitchens\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KitchenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tên nhà bếp')
                    ->required(),
                CheckboxList::make('cookingMethods')
                    ->label('Phương thức nấu')
                    ->relationship(titleAttribute: 'name')
                    ->columns(2)
                    ->gridDirection('row'),
            ]);
    }
}
