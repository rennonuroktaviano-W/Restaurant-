<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Tunai', 'QRIS', 'Kartu Kredit', 'Transfer Bank']),
            'code' => strtoupper(fake()->unique()->bothify('PM-####')),
            'type' => fake()->randomElement(['cash', 'online']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'config' => null,
        ];
    }
}
