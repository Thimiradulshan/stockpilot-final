<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{

public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'movement_type' => StockMovementType::PURCHASE,
            'quantity' => '1.000',
            'quantity_before' => '0.000',
            'quantity_after' => '1.000',
            'reference_type' => null,
            'reference_id' => null,
            'reason' => fake()->sentence(),
            'notes' => null,
            'created_by' => User::factory(),
        ];
    }
}
