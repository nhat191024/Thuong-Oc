<?php

namespace App\Filament\Resources\CookingMethods\Pages;

use App\Filament\Resources\CookingMethods\CookingMethodResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCookingMethods extends ListRecords
{
    protected static string $resource = CookingMethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
