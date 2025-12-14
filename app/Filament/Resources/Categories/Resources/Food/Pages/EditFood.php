<?php

namespace App\Filament\Resources\Categories\Resources\Food\Pages;

use App\Filament\Resources\Categories\Resources\Food\FoodResource;
use App\Filament\Resources\Categories\Pages\ManageCategoryFood;
use App\Filament\Resources\Categories\CategoryResource;

use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Resources\Pages\EditRecord;

class EditFood extends EditRecord
{
    protected static string $resource = FoodResource::class;

    public function getBreadcrumbs(): array
    {
        return [
            CategoryResource::getIndexUrl() => __('Danh mục'),
            $this->getParentRecord()->name,
            ManageCategoryFood::getUrl([$this->getParentRecord()->id]) => __('Quản lý món ăn'),
            __('Chỉnh sửa món ăn'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
