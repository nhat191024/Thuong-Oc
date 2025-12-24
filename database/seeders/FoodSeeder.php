<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Food;

class FoodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(database_path('data/foods.json')), true);
        $demoImgPath = public_path('images/demo.jpg');
        foreach ($data as $item) {
            $food = Food::create($item);
            $food->addMedia($demoImgPath)
                ->preservingOriginal()
                ->toMediaCollection();
        }
    }
}
