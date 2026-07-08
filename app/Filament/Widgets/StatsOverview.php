<?php

namespace App\Filament\Widgets;

use App\Enums\PayStatus;
use App\Models\Bill;
use App\Models\Customer;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return Cache::remember('stats_overview_widget_' . today()->toDateString(), now()->addMinutes(10), function () {
            return [
                Stat::make(
                    __('Tổng doanh thu'),
                    number_format(Bill::where('pay_status', PayStatus::PAID)->sum('final_total')) . ' VND',
                )
                    ->description(__('Doanh thu tất cả các thời điểm'))
                    ->descriptionIcon('heroicon-m-currency-dollar')
                    ->color('success'),
                Stat::make(__('Đơn hàng hôm nay'), Bill::whereDate('created_at', today())->count())
                    ->description(__('Số hóa đơn tạo trong ngày'))
                    ->descriptionIcon('heroicon-m-shopping-bag'),
                Stat::make(
                    __('Doanh thu hôm nay'),
                    number_format(
                        Bill::where('pay_status', PayStatus::PAID)
                            ->whereDate('created_at', today())
                            ->sum('final_total'),
                    ) . ' VND',
                )
                    ->description(__('Doanh thu đã thanh toán trong ngày'))
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),
                Stat::make(__('Tổng số khách hàng'), Customer::count())
                    ->description(__('Tổng số khách hàng đã đăng ký'))
                    ->descriptionIcon('heroicon-m-users'),
            ];
        });
    }
}
