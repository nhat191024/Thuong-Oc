<?php

namespace App\Filament\Resources\Food\Schemas;

use App\Models\Category;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class FoodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label(__('Danh mục'))
                    ->searchable()
                    ->options(
                        fn() => Category::query()
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        Category::query()
                            ->where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        fn($value): ?string =>
                        Category::find($value)?->name
                    )
                    ->columnSpanFull()
                    ->required(),
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
