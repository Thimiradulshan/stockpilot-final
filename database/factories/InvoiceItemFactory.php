<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceItemFactory extends Factory
{

protected $model = InvoiceItem::class;


public function definition(): array
    {
        $quantity = 1.000;
        $unitPrice = 100.00;
        $discount = 0.00;
        $tax = 0.00;

        return [
            'invoice_id' => Invoice::factory(),
            'product_id' => Product::factory()->withQuantity(100.000),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'line_total' => ($quantity * $unitPrice) - $discount + $tax,
        ];
    }
}
