<?php

namespace App\Filament\Resources\Categories\Resources\Food\Schemas;

use App\Models\CookingMethod;
use App\Services\InventoryUnitService;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Schemas\Components\Utilities\Get;

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
                Select::make('inventory_unit_id')
                    ->label(__('Đơn vị tồn kho'))
                    ->options(fn (): array => InventoryUnitService::options())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                TextInput::make('purchase_price')
                    ->label(__('Giá nhập trên mỗi đơn vị'))
                    ->helperText(__('Giá này được dùng để tính thành tiền khi nhân viên nhập kho.'))
                    ->required()
                    ->numeric()
                    ->integer()
                    ->minValue(0)
                    ->prefix('₫'),
                Textarea::make('note')
                    ->label(__('Ghi chú'))
                    ->placeholder(__('Nhập ghi chú cho thực phẩm'))
                    ->columnSpanFull(),
                SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('Ảnh'))
                    ->collection('default')
                    ->conversion('preview')
                    ->disk('public')
                    ->visibility('public')
                    ->image()
                    ->required()
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
                                fn (string $search): array => CookingMethod::query()
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(
                                fn ($value): ?string => CookingMethod::whereId($value)->value('name')
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
                        TextInput::make('ingredient_quantity')
                            ->label(__('Lượng nguyên liệu mỗi phần'))
                            ->helperText(__('Để trống nếu chưa thiết lập định mức.'))
                            ->numeric()
                            ->minValue(0.001)
                            ->step(0.001)
                            ->suffix(function (Get $get): ?string {
                                $unitId = $get('../../inventory_unit_id');

                                return InventoryUnitService::all()->firstWhere('id', (int) $unitId)?->symbol;
                            }),
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
