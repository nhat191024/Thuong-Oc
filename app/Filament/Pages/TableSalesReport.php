<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\TableSalesSummary;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class TableSalesReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.table-sales-report';

    public static function getNavigationLabel(): string
    {
        return __('Doanh thu theo bàn');
    }

    public function getTitle(): string
    {
        return __('Doanh thu theo bàn');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TableSalesSummary::query())
            ->pluralModelLabel(__('báo cáo doanh thu theo bàn'))
            ->columns([
                TextColumn::make('summary_date')
                    ->label(__('Ngày'))
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('branch.name')
                    ->label(__('Cơ sở'))
                    ->sortable(),
                TextColumn::make('table_number')
                    ->label(__('Số bàn'))
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('table_name')
                    ->label(__('Tên bàn'))
                    ->placeholder(__('Không có tên'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('bill_count')
                    ->label(__('Số hóa đơn'))
                    ->numeric()
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Tổng hóa đơn'))->numeric()),
                TextColumn::make('gross_revenue')
                    ->label(__('Trước giảm giá'))
                    ->money('vnd')
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Tổng trước giảm'))->money('vnd')),
                TextColumn::make('discount_amount')
                    ->label(__('Giảm giá'))
                    ->money('vnd')
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Tổng giảm giá'))->money('vnd')),
                TextColumn::make('net_revenue')
                    ->label(__('Thực thu'))
                    ->money('vnd')
                    ->sortable()
                    ->summarize(Sum::make()->label(__('Tổng thực thu'))->money('vnd')),
                TextColumn::make('average_bill_value')
                    ->label(__('Trung bình hóa đơn'))
                    ->state(fn (TableSalesSummary $record): int => $record->bill_count > 0
                        ? (int) round($record->net_revenue / $record->bill_count)
                        : 0)
                    ->money('vnd'),
                TextColumn::make('last_paid_at')
                    ->label(__('Thanh toán gần nhất'))
                    ->dateTime('H:i d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('branch_id')
                    ->label(__('Cơ sở'))
                    ->relationship('branch', 'name')
                    ->default(fn (): ?int => Branch::query()->oldest('id')->value('id'))
                    ->preload()
                    ->searchable(),
                Filter::make('summary_date')
                    ->label(__('Ngày'))
                    ->schema([
                        DatePicker::make('date')
                            ->label(__('Ngày'))
                            ->default(Carbon::yesterday())
                            ->native(false),
                    ])
                    ->default(['date' => Carbon::yesterday()->toDateString()])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->whereDate('summary_date', Carbon::parse(
                            $data['date'] ?? Carbon::yesterday()->toDateString(),
                        )->toDateString()))
                    ->indicateUsing(fn (array $data): string => __('Ngày: :date', [
                        'date' => Carbon::parse($data['date'] ?? Carbon::yesterday()->toDateString())->format('d/m/Y'),
                    ])),
            ])
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('branch_id')
                ->orderByDesc('net_revenue')
                ->orderBy('table_number'))
            ->emptyStateHeading(__('Chưa có dữ liệu doanh thu'))
            ->emptyStateDescription(__('Dữ liệu sẽ xuất hiện sau khi command tổng hợp doanh thu theo bàn chạy.'));
    }
}
