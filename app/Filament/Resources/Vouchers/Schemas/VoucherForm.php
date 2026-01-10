<?php

namespace App\Filament\Resources\Vouchers\Schemas;

use App\Models\Customer;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Thông tin chung'))
                    ->description(__('Các thông tin cơ bản về mã giảm giá'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('Mã giảm giá'))
                            ->placeholder(__('Nhập mã giảm giá'))
                            ->required()
                            ->maxLength(32)
                            ->unique(ignoreRecord: true),
                        Select::make('model_id')
                            ->label(__('Mã dành cho khách hàng cụ thể'))
                            ->searchable()
                            ->default(0)
                            ->getOptionLabelUsing(fn($value): ?string => $value ? Customer::find($value)?->name : 'Tất cả khách hàng')
                            ->options(
                                fn() => [0 => 'Tất cả khách hàng'] + Customer::with('roles')
                                    ->whereHas('roles', function ($query) {
                                        $query->where('name', 'customer');
                                    })
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            )
                            ->getSearchResultsUsing(
                                fn(string $search): array =>
                                [0 => 'Tất cả khách hàng'] + Customer::with('roles')
                                    ->whereHas('roles', function ($query) {
                                        $query->where('name', 'customer');
                                    })
                                    ->where('name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->pluck('name', 'id')
                                    ->toArray()
                            ),
                    ])->columnSpanFull(),

                Section::make(__('Giá trị ưu đãi & Điều kiện'))
                    ->description(__('Cấu hình giá trị giảm giá và các điều kiện áp dụng'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('data.discount_percent')
                                    ->label(__('Phần trăm giảm giá'))
                                    ->placeholder(__('Nhập %'))
                                    ->helperText(__('Nhập phần trăm giảm giá từ 0 đến 100'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%'),
                                TextInput::make('data.max_discount_amount')
                                    ->label(__('Số tiền giảm tối đa'))
                                    ->placeholder(__('Nhập số tiền'))
                                    ->helperText(__('Nhập số tiền giảm tối đa cho mã giảm giá'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₫'),
                                TextInput::make('data.min_order_amount')
                                    ->label(__('Đơn hàng tối thiểu'))
                                    ->placeholder(__('Nhập giá trị'))
                                    ->helperText(__('Giá trị đơn hàng tối thiểu để áp dụng mã'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('₫'),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('Giới hạn sử dụng & Thời gian'))
                    ->description(__('Thiết lập giới hạn số lần sử dụng và thời gian hiệu lực'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('data.usage_limit')
                                    ->label(__('Giới hạn số lần sử dụng'))
                                    ->placeholder(__('Nhập giới hạn'))
                                    ->helperText(__('Nhập giới hạn số lần sử dụng (Tích không giới hạn sẽ bỏ qua giá trị này)'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(fn($get) => !$get('data.is_unlimited'))
                                    ->disabled(fn($get) => $get('data.is_unlimited'))
                                    ->dehydrated(fn($get) => !$get('data.is_unlimited')),
                                Checkbox::make('data.is_unlimited')
                                    ->label(__('Không giới hạn số lần sử dụng'))
                                    ->default(false)
                                    ->live()
                                    ->afterStateUpdated(fn($state, $set) => $state ? $set('data.usage_limit', null) : null)
                                    ->inline(false),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('data.starts_at')
                                    ->label(__('Ngày bắt đầu'))
                                    ->placeholder(__('Chọn ngày bắt đầu'))
                                    ->helperText(__('Thời gian bắt đầu hiệu lực'))
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->default(now()),

                                DateTimePicker::make('expires_at')
                                    ->label(__('Ngày kết thúc'))
                                    ->placeholder(__('Chọn ngày kết thúc'))
                                    ->helperText(__('Thời gian kết thúc (để trống nếu vô hạn)'))
                                    ->native(false)
                                    ->displayFormat('d/m/Y H:i')
                                    ->after('data.starts_at'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
