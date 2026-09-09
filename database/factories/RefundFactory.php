<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_id' => Payment::factory(),
            'amount' => fake()->randomFloat(2, 10000, 1000000),
            'reason_code' => fake()->randomElement(['customer', 'other']),
            'reason' => fake()->sentence(),
            'status' => 'succeeded',
            'created_by' => User::factory(),
        ];
    }
}
