<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseItemFactory extends Factory
{

protected $model = PurchaseItem::class;


public function definition(): array
    {
        $quantity = 10.000;
        $unitCost = 50.00;
        $discount = 0.00;
        $tax = 0.00;

        return [
            'purchase_id' => Purchase::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'line_total' => ($quantity * $unitCost) - $discount + $tax,
        ];
    }
}
