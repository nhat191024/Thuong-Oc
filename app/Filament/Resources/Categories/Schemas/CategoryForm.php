<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Tên'))
                    ->placeholder(__('Nhập tên danh mục'))
                    ->columnSpanFull()
                    ->required(),
                Textarea::make('note')
                    ->label(__('Ghi chú'))
                    ->placeholder(__('Nhập ghi chú cho danh mục'))
                    ->columnSpanFull(),
            ]);
    }
}
