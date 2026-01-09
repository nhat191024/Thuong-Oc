<?php

namespace Database\Seeders;

use App\Enums\Role;

use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role as RoleModel;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Role::cases() as $role) {
            RoleModel::firstOrCreate(['name' => $role->value]);
        }
    }
}
