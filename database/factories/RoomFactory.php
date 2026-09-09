<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'area_id' => Area::factory(),
            'room_number' => fake()->unique()->numerify('ROOM-###'),
            'name' => fake()->randomElement(['Deluxe', 'Superior', 'Villa', 'Suite']),
            'status' => 'available',
            'token_qr' => null,
            'is_active' => true,
        ];
    }
}
