<?php

namespace App\Filament\Resources\Staffs\Tables;

use App\Enums\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StaffsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username')
                    ->label(__('Tên đăng nhập'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('Họ và tên'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('Số điện thoại'))
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label(__('Chi nhánh'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('Vai trò'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Role::STAFF->value => __('Nhân viên'),
                        Role::KITCHEN->value => __('Bếp'),
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        Role::STAFF->value => 'info',
                        Role::KITCHEN->value => 'warning',
                        default => 'gray',
                    }),
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
                SelectFilter::make('role')
                    ->label(__('Vai trò'))
                    ->relationship('roles', 'name')
                    ->options([
                        Role::STAFF->value => __('Nhân viên'),
                        Role::KITCHEN->value => __('Bếp'),
                    ]),
                SelectFilter::make('branch_id')
                    ->label(__('Chi nhánh'))
                    ->relationship('branch', 'name'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
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
