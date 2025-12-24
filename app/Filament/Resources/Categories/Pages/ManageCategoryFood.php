<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Resources\Food\FoodResource;

use App\Enums\CacheKeys;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;

use Filament\Resources\Pages\ManageRelatedRecords;

use Illuminate\Support\Facades\Cache;


class ManageCategoryFood extends ManageRelatedRecords
{
    protected static string $resource = CategoryResource::class;

    protected static string $relationship = 'food';

    protected static ?string $relatedResource = FoodResource::class;

    public function getTitle(): string
    {
        return __('Quản lý món ăn danh mục :name', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            CategoryResource::getIndexUrl() => __('Danh mục'),
            $this->getRecord()->name,
            __('Quản lý món ăn'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table;
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order);

        Cache::forget(CacheKeys::MENUS->value);
        Cache::forget(CacheKeys::MENU_CATEGORIES->value);
    }
}
