<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->bothify('setting_####'),
            'value' => fake()->sentence(),
            'label' => fake()->words(3, true),
            'group' => 'general',
            'is_encrypted' => false,
        ];
    }
}
