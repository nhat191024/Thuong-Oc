<?php

namespace App\Filament\Widgets;

use App\Models\BillDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class PopularDishes extends ChartWidget
{
    protected ?string $heading = 'Món ăn phổ biến nhất';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        return Cache::remember('popular_dishes_widget', now()->addHour(), function () {
            $data = BillDetail::selectRaw('sum(quantity) as total_quantity, dish_id')
                ->groupBy('dish_id')
                ->orderByDesc('total_quantity')
                ->limit(10)
                ->with(['dish.food', 'dish.cookingMethod'])
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => __('Số lượng đã bán'),
                        'data' => $data->pluck('total_quantity')->toArray(),
                    ],
                ],
                'labels' => $data->map(function (BillDetail $detail) {
                    $foodName = $detail->dish?->food?->name ?? 'Unknown Food';
                    $methodName = $detail->dish?->cookingMethod?->name;

                    return $methodName ? "$foodName - $methodName" : $foodName;
                })->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
