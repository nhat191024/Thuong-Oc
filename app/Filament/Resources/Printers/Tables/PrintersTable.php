<?php

namespace App\Filament\Resources\Printers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PrintersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('branch.name')
                    ->label(__('Chi nhánh'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('Tên máy in'))
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label(__('Địa chỉ IP'))
                    ->searchable(),
                TextColumn::make('port')
                    ->label(__('Cổng'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Hoạt động'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Tạo lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label(__('Chi nhánh'))
                    ->relationship('branch', 'name'),
                TernaryFilter::make('is_active')
                    ->label(__('Hoạt động')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
