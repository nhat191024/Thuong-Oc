<?php

namespace App\Filament\Resources\InventoryUnits\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Tên đơn vị'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('symbol')
                    ->label(__('Ký hiệu'))
                    ->searchable(),
                TextColumn::make('foods_count')
                    ->label(__('Số loại thực phẩm'))
                    ->counts('foods'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn ($record): bool => (bool) $record->foods_exists)
                    ->tooltip(fn ($record): ?string => $record->foods_exists ? __('Đơn vị đang được thực phẩm sử dụng.') : null),
            ]);
    }
}
