<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->numerify('ORD-#####'),
            'order_type' => fake()->randomElement(['dine_in', 'take_away', 'room_service']),
            'subtotal' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'service_charge_amount' => 0,
            'grand_total' => 0,
            'order_status' => Order::STATUS_NEW,
            'payment_status' => Order::PAYMENT_PENDING,
            'created_by' => User::factory(),
            'ordered_at' => now(),
        ];
    }

    public function dineIn(): static
    {
        return $this->state(fn () => [
            'order_type' => Order::TYPE_DINE_IN,
            'table_id' => DiningTable::factory(),
            'room_id' => null,
        ]);
    }

    public function roomService(): static
    {
        return $this->state(fn () => [
            'order_type' => Order::TYPE_ROOM_SERVICE,
            'table_id' => null,
            'room_id' => Room::factory(),
            'area_id' => Area::factory(),
        ]);
    }

    public function takeAway(): static
    {
        return $this->state(fn () => [
            'order_type' => Order::TYPE_TAKE_AWAY,
            'table_id' => null,
            'room_id' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'order_status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
            'completed_at' => now(),
        ]);
    }
}
