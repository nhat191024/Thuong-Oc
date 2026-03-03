<?php

namespace App\Filament\Resources\Branches\Resources\Tables\Tables;

use App\Models\Table as TableModel;
use Illuminate\Database\Eloquent\Builder;

use App\Enums\TableActiveStatus;

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

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('Mã Bàn'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('table_number')
                    ->label(__('Số Bàn'))
                    ->searchable(),
                TextColumn::make('is_active')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->label(__('Trạng Thái'))
                    ->badge()
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->label(__('Xóa Lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('Tạo Lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('Cập Nhật Lúc'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->default('trashed'),
            ])
            ->recordActions([
                Action::make('qr_code')
                    ->label(__('Mã QR'))
                    ->icon('heroicon-o-qr-code')
                    ->modalHeading(fn (TableModel $record): string => __('Mã QR - Bàn :number', ['number' => $record->table_number]))
                    ->modalContent(fn (TableModel $record) => view('filament.tables.qr-code-modal', [
                        'qrCode' => base64_encode(QrCode::format('png')->size(300)->generate(route('customer-menu.index', ['tableId' => $record->id]))),
                        'url' => route('customer-menu.index', ['tableId' => $record->id]),
                        'tableNumber' => $record->table_number,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel(__('Đóng')),
                DeleteAction::make()
                    ->disabled(fn (TableModel $record): bool => $record->is_active == TableActiveStatus::ACTIVE)
                    ->tooltip(fn (TableModel $record): ?string => $record->is_active == TableActiveStatus::ACTIVE ? __('Không thể ẩn bàn đang hoạt động') : null),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->defaultSort('table_number', 'asc');
    }
}
