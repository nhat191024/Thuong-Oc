<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label(__('Tên đăng nhập'))
                    ->searchable(),
                // TextColumn::make('email')
                //     ->label('Email address')
                //     ->searchable(),
                TextColumn::make('name')
                    ->label(__('Họ và tên'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Số điện thoại'))
                    ->searchable(),
                // TextColumn::make('avatar')
                //     ->searchable(),
                TextColumn::make('points')
                    ->label(__('Điểm thưởng'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->label(__('Xóa vào'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Tạo vào'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Cập nhật vào'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                // EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
