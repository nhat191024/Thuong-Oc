<?php

namespace App\Filament\Resources\Categories\Resources\Food\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

use Filament\Tables\Filters\TrashedFilter;

class FoodTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label(__('Thứ tự')),
                TextColumn::make('name')
                    ->label(__('Tên'))
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('Giá'))
                    ->money('vnd'),
                TextColumn::make('discount_price')
                    ->label(__('Giá khuyến mãi'))
                    ->money('vnd'),
                TextColumn::make('is_favorite')
                    ->label(__('Yêu thích'))
                    ->formatStateUsing(fn($state) => $state ? __('Có') : __('Không'))
                    ->color(fn($state): string => $state ? 'success' : 'primary')
                    ->badge(),
                TextColumn::make('deleted_at')
                    ->label(__('Đã xóa lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Tạo lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Cập nhật lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed')
                    ->native(false)
            ])
            ->recordActions([
                Action::make('Set-as-Favorite')
                    ->icon('heroicon-s-heart')
                    ->label(__('Đánh dấu yêu thích'))
                    ->tooltip(__('Món ăn được đánh dấu là yêu thích sẽ xếp lên đầu'))
                    ->action(fn($record) => $record->update(['is_favorite' => true]))
                    ->visible(fn($record) => !$record->is_favorite && $record->deleted_at === null),
                Action::make('Unset-as-Favorite')
                    ->icon('heroicon-o-x-circle')
                    ->label(__('Bỏ đánh dấu yêu thích'))
                    ->tooltip(__('Bỏ đánh dấu món ăn này là món được yêu thích'))
                    ->action(fn($record) => $record->update(['is_favorite' => false]))
                    ->visible(fn($record) => $record->is_favorite && $record->deleted_at === null),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()
                    ->visible(fn($record) => $record->deleted_at !== null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}
