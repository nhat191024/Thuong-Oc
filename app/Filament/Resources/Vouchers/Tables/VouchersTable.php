<?php

namespace App\Filament\Resources\Vouchers\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\DeleteAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

class VouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('Mã giảm giá'))
                    ->searchable(),
                TextColumn::make('data.discount_percent')
                    ->label(__('Phần trăm giảm giá'))
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable()
                    ->placeholder(__('Giảm theo số tiền')),
                TextColumn::make('data.max_discount_amount')
                    ->label(__('Số tiền giảm tối đa'))
                    ->formatStateUsing(fn($state) => '₫' . number_format($state, 0, ',', '.'))
                    ->sortable()
                    ->placeholder(__('Giảm theo %')),
                TextColumn::make('data.min_order_amount')
                    ->label(__('Giá trị đơn hàng tối thiểu'))
                    ->formatStateUsing(fn($state) =>  '₫' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('data.usage_limit')
                    ->label(__('Giới hạn số lần sử dụng'))
                    ->sortable()
                    ->default(__('Không giới hạn')),
                TextColumn::make('data.times_used')
                    ->label(__('Số lần đã sử dụng'))
                    ->sortable()
                    ->default(0),
                TextColumn::make('data.starts_at')
                    ->label(__('Ngày bắt đầu'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('expires_at')
                    ->label(__('Ngày kết thúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Ngày tạo'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Ngày cập nhật'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                ForceDeleteAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
