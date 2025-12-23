<?php

namespace App\Filament\Resources\Bills\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class BillInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Thông tin chung'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('table.table_number')
                                    ->label(__('Bàn'))
                                    ->icon('heroicon-o-table-cells'),
                                TextEntry::make('branch.name')
                                    ->label(__('Chi nhánh'))
                                    ->icon('heroicon-o-building-storefront'),
                                TextEntry::make('user.name')
                                    ->label(__('Nhân viên'))
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('time_in')
                                    ->label(__('Giờ vào'))
                                    ->dateTime('H:i d/m/Y'),
                                TextEntry::make('time_out')
                                    ->label(__('Giờ ra'))
                                    ->dateTime('H:i d/m/Y')
                                    ->placeholder(__('Chưa ra')),
                            ]),
                    ]),
                Section::make(__('Chi tiết đơn hàng'))
                    ->schema([
                        ViewEntry::make('billDetails')
                            ->view('filament.resources.bills.infolists.bill-details')
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Thanh toán'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total')
                                    ->label(__('Tổng tiền'))
                                    ->money('VND'),
                                TextEntry::make('discount')
                                    ->label(__('Giảm giá'))
                                    ->money('VND')
                                    ->placeholder(__('0 ₫')),
                                TextEntry::make('final_total')
                                    ->label(__('Thực thu'))
                                    ->money('VND')
                                    ->weight(FontWeight::Bold)
                                    ->size(TextSize::Large)
                                    ->color('primary'),
                                TextEntry::make('payment_method')
                                    ->label(__('Phương thức thanh toán'))
                                    ->formatStateUsing(fn($state) => $state?->label() ?? 'Chưa chọn')
                                    ->badge(),
                                TextEntry::make('pay_status')
                                    ->label(__('Trạng thái thanh toán'))
                                    ->formatStateUsing(fn($state) => $state->label())
                                    ->badge(),
                            ]),
                    ]),
            ]);
    }
}
