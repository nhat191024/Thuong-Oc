<?php

namespace App\Filament\Resources\InventoryUnits\Pages;

use App\Filament\Resources\InventoryUnits\InventoryUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInventoryUnit extends EditRecord
{
    protected static string $resource = InventoryUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->disabled(fn (): bool => $this->record->foods()->exists())
                ->tooltip(fn (): ?string => $this->record->foods()->exists() ? __('Đơn vị đang được thực phẩm sử dụng.') : null),
        ];
    }
}
