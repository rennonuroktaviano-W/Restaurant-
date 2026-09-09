<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'category_id' => Category::factory(),
            'sku' => 'SKU-'.strtoupper(Str::random(8)),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'image' => null,
            'cost_price' => fake()->randomFloat(2, 1000, 50000),
            'sale_price' => fake()->randomFloat(2, 5000, 100000),
            'stock_type' => fake()->randomElement(['limited', 'unlimited']),
            'stock' => function () {
                return fake()->numberBetween(0, 200);
            },
            'is_active' => true,
            'is_available' => true,
            'is_featured' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 50),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
