<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->word();

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'type' => fake()->randomElement(['restaurant', 'pool', 'room', 'villa', 'other']),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
