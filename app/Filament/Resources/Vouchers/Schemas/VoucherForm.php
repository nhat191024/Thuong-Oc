<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('Mã giảm giá'))
                    ->placeholder(__('Nhập mã giảm giá'))
                    ->required()
                    ->maxLength(32)
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull(),
                TextInput::make('data.discount_percent')
                    ->label(__('Phần trăm giảm giá'))
                    ->placeholder(__('Nhập phần trăm giảm giá'))
                    ->helperText(__('Nhập phần trăm giảm giá từ 0 đến 100'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                TextInput::make('data.max_discount_amount')
                    ->label(__('Số tiền giảm tối đa'))
                    ->placeholder(__('Nhập số tiền giảm tối đa'))
                    ->helperText(__('Nhập số tiền giảm tối đa cho mã giảm giá'))
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₫'),
                TextInput::make('data.min_order_amount')
                    ->label(__('Giá trị đơn hàng tối thiểu'))
                    ->placeholder(__('Nhập giá trị đơn hàng tối thiểu'))
                    ->helperText(__('Nhập giá trị đơn hàng tối thiểu để áp dụng mã giảm giá'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('₫'),
                TextInput::make('data.usage_limit')
                    ->label(__('Giới hạn số lần sử dụng'))
                    ->placeholder(__('Nhập giới hạn số lần sử dụng'))
                    ->helperText(__('Nhập giới hạn số lần sử dụng cho mã giảm giá (Tích không giới hạn sẽ bỏ qua giá trị này)'))
                    ->numeric()
                    ->minValue(1)
                    ->required(fn($get) => !$get('data.is_unlimited'))
                    ->disabled(fn($get) => $get('data.is_unlimited'))
                    ->dehydrated(fn($get) => !$get('data.is_unlimited')),
                Checkbox::make('data.is_unlimited')
                    ->label(__('Mã giảm giá không giới hạn số lần sử dụng'))
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(fn($state, $set) => $state ? $set('data.usage_limit', null) : null),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('data.starts_at')
                            ->label(__('Ngày bắt đầu'))
                            ->placeholder(__('Nhập ngày bắt đầu'))
                            ->helperText(__('Chọn ngày bắt đầu có hiệu lực của mã giảm giá'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now()),

                        DateTimePicker::make('expires_at')
                            ->label(__('Ngày kết thúc'))
                            ->placeholder(__('Nhập ngày kết thúc'))
                            ->helperText(__('Chọn ngày kết thúc của mã giảm giá (hãy để trống nếu không có hạn sử dụng)'))
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->after('data.starts_at'),
                    ]),
            ]);
    }
}
