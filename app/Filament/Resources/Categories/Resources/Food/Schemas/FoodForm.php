<?php

namespace App\Filament\Resources\Categories\Resources\Food\Schemas;

use App\Models\Category;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class FoodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Tên'))
                    ->placeholder(__('Nhập tên thực phẩm'))
                    ->required(),
                TextInput::make('price')
                    ->label(__('Giá'))
                    ->placeholder(__('Nhập giá thực phẩm'))
                    ->required()
                    ->numeric()
                    ->prefix('₫'),
                Textarea::make('note')
                    ->label(__('Ghi chú'))
                    ->placeholder(__('Nhập ghi chú cho thực phẩm'))
                    ->columnSpanFull(),
            ]);
    }
}
