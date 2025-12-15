<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Enums\CacheKeys;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Support\Facades\Cache;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function reorderTable(array $order, string|int|null $draggedRecordKey = null): void
    {
        parent::reorderTable($order);

        Cache::forget(CacheKeys::MENUS->value);
        Cache::forget(CacheKeys::MENU_CATEGORIES->value);
    }
}
