<?php

namespace App\Filament\Resources\CookingMethods\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\TrashedFilter;

class CookingMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Tên phương pháp nấu'))
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->label(__('Xóa lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Tạo lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Cập nhật lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->disabled(fn($record): bool => $record->dishes_exists)
                    ->tooltip(fn($record): ?string => $record->dishes_exists ? __('Đang có món ăn sử dụng phương pháp nấu này') : null),
                RestoreAction::make(),
                ForceDeleteAction::make()
                    ->disabled(fn($record): bool => $record->dishes_exists)
                    ->tooltip(fn($record): ?string => $record->dishes_exists ? __('Đang có món ăn sử dụng phương pháp nấu này') : null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
