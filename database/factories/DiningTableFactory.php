<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\DiningTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiningTable>
 */
class DiningTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'table_number' => fake()->unique()->numerify('T###'),
            'name' => fake()->randomElement(['Meja', 'Meja dekat jendela', 'Meja VIP']),
            'status' => 'available',
            'token_qr' => null,
            'is_active' => true,
        ];
    }
}
