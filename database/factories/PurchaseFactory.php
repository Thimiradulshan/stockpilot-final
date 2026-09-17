<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseFactory extends Factory
{

protected $model = Purchase::class;


public function definition(): array
    {
        $subtotal = 1000.00;
        $discount = 0.00;
        $tax = 0.00;

        return [
            'purchase_number' => fake()->unique()->numerify('PUR-########'),
            'supplier_id' => Supplier::factory(),
            'purchase_date' => today(),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $subtotal - $discount + $tax,
            'status' => 'completed',
            'notes' => null,
            'created_by' => User::factory()->state([
                'role' => UserRole::STOCK,
            ]),
        ];
    }


public function cancelled(): static
    {
        return $this->state([
            'status' => 'cancelled',
        ]);
    }
}
