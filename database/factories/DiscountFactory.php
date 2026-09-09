<?php

namespace Database\Factories;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Discount>
 */
class DiscountFactory extends Factory
{
    public function definition(): array
    {
        $isPercentage = fake()->boolean();

        return [
            'name' => fake()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('PROMO####')),
            'type' => $isPercentage ? 'percentage' : 'fixed',
            'value' => $isPercentage ? fake()->numberBetween(5, 50) : fake()->randomFloat(2, 5000, 25000),
            'min_amount' => fake()->randomElement([0, 50000, 100000]),
            'max_amount' => null,
            'is_automatic' => fake()->boolean(),
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(30),
            'is_active' => true,
        ];
    }
}
