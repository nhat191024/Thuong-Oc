<?php

namespace App\Filament\Resources\Staffs\Pages;

use App\Filament\Resources\Staffs\StaffResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role'] = $this->record->roles->first()?->name;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->role = $data['role'] ?? null;
        unset($data['role']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->role) {
            $this->record->syncRoles([$this->role]);
        }
    }
}
