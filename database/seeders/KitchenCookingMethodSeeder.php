<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\KitchenCookingMethod;

class KitchenCookingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/kitchen_cooking_methods.json')), true);

        foreach ($data as $item) {
            KitchenCookingMethod::create($item);
        }
    }
}
