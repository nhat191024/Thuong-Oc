<?php

namespace App\Filament\Pages;

use App\Models\DishSalesSummary;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DishSalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.dish-sales-report';

    public static function getNavigationLabel(): string
    {
        return __('Doanh thu theo món');
    }

    public function getTitle(): string
    {
        return __('Doanh thu theo món và cách nấu');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(DishSalesSummary::query())
            ->pluralModelLabel(__('báo cáo doanh thu theo món'))
            ->columns([
                TextColumn::make('food_name')
                    ->label(__('Món'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cooking_method_name')
                    ->label(__('Cách nấu'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_quantity')
                    ->label(__('Số bán ra'))
                    ->numeric()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label(__('Tổng số bán ra'))
                            ->numeric(),
                    ),
                TextColumn::make('total_revenue')
                    ->label(__('Tổng doanh thu'))
                    ->money('vnd')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label(__('Tổng doanh thu'))
                            ->money('vnd'),
                    ),
                TextColumn::make('last_ordered_at')
                    ->label(__('Bán gần nhất'))
                    ->dateTime('H:i d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('calculated_at')
                    ->label(__('Tính lúc'))
                    ->dateTime('H:i d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('food_name')
                ->orderByDesc('total_revenue')
                ->orderBy('cooking_method_name'))
            ->emptyStateHeading(__('Chưa có dữ liệu doanh thu'))
            ->emptyStateDescription(__('Dữ liệu sẽ xuất hiện sau khi command tổng hợp doanh thu chạy.'));
    }
}
