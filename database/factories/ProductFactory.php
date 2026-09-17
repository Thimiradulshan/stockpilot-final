<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{

public function definition(): array
    {
        $costPrice = fake()->randomFloat(2, 10, 5000);
        $sellingPrice = fake()->randomFloat(
            2,
            max($costPrice, 10),
            max($costPrice + 1, 10000),
        );

        return [
            'category_id' => Category::factory(),
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-????')),
            'cost_price' => $costPrice,
            'selling_price' => $sellingPrice,
            'quantity' => fake()->randomFloat(3, 0, 500),
            'reorder_level' => fake()->randomFloat(3, 0, 50),
            'description' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }


public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }


public function withQuantity(float $quantity): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }


public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }
}
