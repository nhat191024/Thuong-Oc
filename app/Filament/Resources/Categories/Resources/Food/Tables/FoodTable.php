<?php

namespace App\Filament\Resources\Categories\Resources\Food\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

use Filament\Forms\Components\TextInput;

use Filament\Tables\Filters\TrashedFilter;

class FoodTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label(__('Thứ tự')),
                SpatieMediaLibraryImageColumn::make('default')
                    ->label(__('Ảnh'))
                    ->conversion('preview'),
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
                TextColumn::make('sold_count')
                    ->label(__('Số lượng đã bán')),
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
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()
                    ->visible(fn($record) => $record->deleted_at !== null),
                ActionGroup::make([
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
                    Action::make('set-discount-price')
                        ->icon('heroicon-s-tag')
                        ->label(__('Áp khuyến mãi'))
                        ->tooltip(__('Áp khuyến mãi cho món ăn này'))
                        ->modalHeading(__('Áp khuyến mãi cho món ăn'))
                        ->schema([
                            TextInput::make('discount_price')
                                ->label(__('Giá khuyến mãi mới'))
                                ->numeric()
                                ->required()
                                ->prefix('₫'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update(['discount_price' => $data['discount_price']]);
                        })
                        ->visible(fn($record) => $record->discount_price == 0 && $record->deleted_at === null),
                    Action::make('unset-discount-price')
                        ->icon('heroicon-o-x-circle')
                        ->label(__('Bỏ đánh giá khuyến mãi'))
                        ->tooltip(__('Bỏ đánh giá khuyến mãi cho món ăn này'))
                        ->action(fn($record) => $record->update(['discount_price' => 0]))
                        ->visible(fn($record) => $record->discount_price > 0 && $record->deleted_at === null),
                ]),
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
