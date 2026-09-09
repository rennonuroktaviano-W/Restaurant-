<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_method_id' => PaymentMethod::factory(),
            'type' => 'cash',
            'status' => Payment::STATUS_PENDING,
            'amount' => 0,
            'created_by' => User::factory(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }
}
