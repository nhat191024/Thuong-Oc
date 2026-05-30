<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\BranchFoodStock;
use App\Models\Food;

use App\Services\MenuService;

use BackedEnum;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageBranchFoodStock extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.manage-branch-food-stock';

    public ?int $selectedBranchId = null;

    public function mount(): void
    {
        $this->selectedBranchId = auth()->user()->branch_id
            ?? Branch::query()->first()?->id;
    }

    public static function getNavigationLabel(): string
    {
        return __('Tồn kho chi nhánh');
    }

    public function getTitle(): string
    {
        return __('Quản lý tồn kho theo chi nhánh');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('selectedBranchId')
                    ->label(__('Chi nhánh'))
                    ->options(Branch::query()->pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetTable()),
            ])
            ->columns(3);
    }

    public function table(Table $table): Table
    {
        $branchId = $this->selectedBranchId;

        return $table
            ->query(
                Food::query()
                    ->with(['category:id,name'])
                    ->whereHas('dishes')
                    ->orderBy('order')
            )
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('Danh mục'))
                    ->sortable(),
                TextColumn::make('name')
                    ->label(__('Món ăn'))
                    ->searchable(),
                TextColumn::make('price')
                    ->label(__('Giá'))
                    ->money('vnd'),
                IconColumn::make('is_out_of_stock')
                    ->label(__('Hết món'))
                    ->state(function (Food $record) use ($branchId): bool {
                        if ($branchId === null) {
                            return false;
                        }

                        return BranchFoodStock::whereBranchId($branchId)
                            ->whereFoodId($record->id)
                            ->value('is_out_of_stock') ?? false;
                    })
                    ->boolean()
                    ->trueIcon(Heroicon::XCircle)
                    ->falseIcon(Heroicon::CheckCircle)
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->recordActions([
                Action::make('markOutOfStock')
                    ->label(__('Đánh dấu hết món'))
                    ->icon(Heroicon::XCircle)
                    ->color('danger')
                    ->action(function (Food $record) use ($branchId): void {
                        BranchFoodStock::updateOrCreate(
                            ['branch_id' => $branchId, 'food_id' => $record->id],
                            ['is_out_of_stock' => true]
                        );

                        Notification::make()
                            ->title(__('Đã đánh dấu hết món'))
                            ->success()
                            ->send();
                    })
                    ->visible(function (Food $record) use ($branchId): bool {
                        if ($branchId === null) {
                            return false;
                        }

                        return ! (BranchFoodStock::whereBranchId($branchId)
                            ->whereFoodId($record->id)
                            ->value('is_out_of_stock') ?? false);
                    }),
                Action::make('markAvailable')
                    ->label(__('Đánh dấu còn món'))
                    ->icon(Heroicon::CheckCircle)
                    ->color('success')
                    ->action(function (Food $record) use ($branchId): void {
                        BranchFoodStock::whereBranchId($branchId)
                            ->whereFoodId($record->id)
                            ->update(['is_out_of_stock' => false]);

                        MenuService::forgetBranchStockCache($branchId);

                        Notification::make()
                            ->title(__('Đã đánh dấu còn món'))
                            ->success()
                            ->send();
                    })
                    ->visible(function (Food $record) use ($branchId): bool {
                        if ($branchId === null) {
                            return false;
                        }

                        return BranchFoodStock::whereBranchId($branchId)
                            ->whereFoodId($record->id)
                            ->value('is_out_of_stock') ?? false;
                    }),
            ])
            ->defaultSort('category.name');
    }
}
