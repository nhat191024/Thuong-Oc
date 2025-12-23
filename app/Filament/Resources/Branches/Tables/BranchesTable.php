<?php

namespace App\Filament\Resources\Branches\Tables;

use App\Filament\Resources\Branches\BranchResource;

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

class BranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Tên chi nhánh'))
                    ->searchable(),
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
                Action::make('manage-table')
                    ->label(__('Quản lý bàn'))
                    ->url(fn($record): string => BranchResource::getUrl('table-management', ['record' => $record->id]))
                    ->visible(fn($record): bool => $record->deleted_at === null),
                Action::make('manage-kitchen')
                    ->label(__('Quản lý nhà bếp'))
                    ->url(fn($record): string => BranchResource::getUrl('kitchen-management', ['record' => $record->id]))
                    ->visible(fn($record): bool => $record->deleted_at === null),
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
