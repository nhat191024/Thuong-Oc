<?php

namespace App\Filament\Resources\Categories\Resources\Food\Schemas;

use App\Models\CookingMethod;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;

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
                Repeater::make('dishes')
                    ->label(__('Cách chế biến'))
                    ->relationship('dishes')
                    ->schema([
                        Select::make('cooking_method_id')
                            ->label(__('Cách chế biến'))
                            ->options(
                                CookingMethod::query()
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getSearchResultsUsing(
                                fn(string $search): array =>
                                CookingMethod::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(
                                fn($value): ?string =>
                                CookingMethod::find($value)?->name
                            )
                            ->searchable()
                            ->preload(true)
                            ->native(false)
                            ->required(),
                        TextInput::make('additional_price')
                            ->label(__('Giá thêm'))
                            ->numeric()
                            ->default(0)
                            ->prefix('₫'),
                        TextInput::make('note')
                            ->label(__('Ghi chú'))
                            ->placeholder(__('Nhập ghi chú cho cách chế biến'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
