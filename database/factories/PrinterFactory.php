<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Printer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Printer>
 */
class PrinterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Branch::factory(),
            'name' => 'May in ' . fake()->word(),
            'ip_address' => fake()->localIpv4(),
            'port' => 9100,
            'timeout' => 3,
            'character_table' => 27,
            'character_encoding' => 'CP1258',
            'is_active' => true,
        ];
    }
}
