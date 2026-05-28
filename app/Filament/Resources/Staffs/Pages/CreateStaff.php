<?php

namespace App\Filament\Resources\Staffs\Pages;

use App\Filament\Resources\Staffs\StaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->role = $data['role'] ?? null;
        unset($data['role']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->role) {
            $this->record->syncRoles([$this->role]);
        }
    }
}
