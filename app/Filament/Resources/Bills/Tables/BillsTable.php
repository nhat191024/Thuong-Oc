<?php

namespace App\Filament\Resources\Bills\Tables;

use App\Enums\PayStatus;
use App\Enums\PaymentMethods;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
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

class BillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Mã hoá đơn')
                    ->sortable(),
                TextColumn::make('table.table_number')
                    ->label('Bàn Số'),
                TextColumn::make('branch.name')
                    ->label('Chi nhánh')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nhân viên')
                    ->searchable(),
                TextColumn::make('time_in')
                    ->label('Thời gian vào')
                    ->dateTime('H:i d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('time_out')
                    ->label('Thời gian ra')
                    ->dateTime('H:i d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total')
                    ->label('Tổng tiền')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label('Giảm giá')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('final_total')
                    ->label('Thành tiền')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Phương thức thanh toán')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state?->label() ?? 'Chưa chọn')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pay_status')
                    ->label('Trạng thái thanh toán')
                    ->badge()
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->colors([
                        'success' => PayStatus::PAID,
                        'warning' => PayStatus::UNPAID,
                        'danger' => PayStatus::CANCELLED,
                    ]),
                TextColumn::make('created_at')
                    ->label('Tạo lúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Cập nhật lúc')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
