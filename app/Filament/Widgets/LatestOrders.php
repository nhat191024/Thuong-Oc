<?php

namespace App\Filament\Widgets;

use App\Models\Bill;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Cache;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Đơn hàng mới nhất';

    protected static ?string $pollingInterval = '300s';

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $latestIds = Cache::remember('latest_orders_widget_ids', now()->addMinutes(5), function () {
                    return Bill::latest()
                        ->limit(5)
                        ->pluck('id')
                        ->toArray();
                });

                if (empty($latestIds)) {
                    return Bill::query()->whereRaw('1 = 0');
                }

                return Bill::whereIn('id', $latestIds)
                    ->orderByRaw('FIELD(id, ' . implode(',', $latestIds) . ')')
                    ->with(['branch', 'table', 'customer']);
            })
            ->columns([
                TextColumn::make('id')
                    ->label(__('Mã đơn hàng'))
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label(__('Chi nhánh'))
                    ->sortable()
                    ->numeric(),
                TextColumn::make('table.table_number')
                    ->label(__('Bàn số'))
                    ->sortable()
                    ->numeric(),
                TextColumn::make('customer.name')
                    ->label(__('Khách hàng'))
                    ->searchable()
                    ->placeholder(__('Khách vãng lai')),
                TextColumn::make('final_total')
                    ->label(__('Tổng tiền'))
                    ->money('VND')
                    ->sortable(),
                TextColumn::make('pay_status')
                    ->formatStateUsing(fn($state) => $state->label())
                    ->label(__('Trạng thái'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('Tạo lúc'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
