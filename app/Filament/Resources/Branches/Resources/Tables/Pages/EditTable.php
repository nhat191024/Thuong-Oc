<?php

namespace App\Filament\Resources\Branches\Resources\Tables\Pages;

use App\Filament\Resources\Branches\Resources\Tables\TableResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTable extends EditRecord
{
    protected static string $resource = TableResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
