<?php

namespace App\Filament\Resources\Categories\Resources\Food\Pages;

use App\Filament\Resources\Categories\Resources\Food\FoodResource;
use App\Filament\Resources\Categories\Pages\ManageCategoryFood;
use App\Filament\Resources\Categories\CategoryResource;

use Filament\Resources\Pages\CreateRecord;

class CreateFood extends CreateRecord
{
    protected static string $resource = FoodResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            CategoryResource::getIndexUrl() => __('Danh mục'),
            $this->getParentRecord()->name,
            ManageCategoryFood::getUrl([$this->getParentRecord()->id]) => __('Quản lý món ăn'),
            __('Tạo món ăn'),
        ];
    }
}
