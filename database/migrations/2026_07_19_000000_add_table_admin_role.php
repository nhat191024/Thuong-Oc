<?php

use Illuminate\Database\Migrations\Migration;
use App\Enums\Role;
use Spatie\Permission\Models\Role as RoleModel;

return new class extends Migration
{
    public function up(): void
    {
        RoleModel::findOrCreate(Role::TABLE_ADMIN->value, 'web');
    }

    public function down(): void
    {
        RoleModel::query()
            ->where('name', Role::TABLE_ADMIN->value)
            ->where('guard_name', 'web')
            ->delete();
    }
};
