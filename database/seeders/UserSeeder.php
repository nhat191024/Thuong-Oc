<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/users.json')), true);

        foreach ($data as $item) {
            $user = User::create([
                'username' => $item['username'],
                'password' => bcrypt($item['password']),
                'branch_id' => $item['branch_id'],
            ]);

            switch ($item['role']) {
                case '1':
                    $user->assignRole('admin');
                    break;
                case '2':
                    $user->assignRole('staff');
                    break;
                case '3':
                    $user->assignRole('kitchen');
                    break;
            }
        }
    }
}
