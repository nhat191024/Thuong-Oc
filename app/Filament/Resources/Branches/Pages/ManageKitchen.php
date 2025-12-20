<?php

namespace App\Filament\Resources\Branches\Pages;

use App\Filament\Resources\Branches\BranchResource;
use App\Filament\Resources\Branches\Resources\Kitchens\KitchenResource;

use Filament\Tables\Table;
use Filament\Actions\CreateAction;

use Filament\Resources\Pages\ManageRelatedRecords;

class ManageKitchen extends ManageRelatedRecords
{
    protected static string $resource = BranchResource::class;

    protected static string $relationship = 'kitchens';

    protected static ?string $relatedResource = KitchenResource::class;

    public function getTitle(): string
    {
        return __('Quản lý bếp chi nhánh :name', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            BranchResource::getIndexUrl() => __('Chi nhánh'),
            $this->getRecord()->name,
            __('Quản lý bếp'),
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
}
