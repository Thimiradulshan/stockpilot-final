<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{

protected $model = Invoice::class;


public function definition(): array
    {
        $subtotal = 1000.00;
        $discount = 0.00;
        $taxRate = 0.00;
        $taxAmount = 0.00;

        return [
            'invoice_number' => fake()->unique()->numerify('INV-########'),
            'customer_id' => Customer::factory(),
            'invoice_date' => today(),
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_amount' => $subtotal - $discount + $taxAmount,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'notes' => null,
            'created_by' => User::factory()->state([
                'role' => UserRole::SALES,
            ]),
        ];
    }


public function voided(): static
    {
        return $this->state([
            'status' => 'voided',
        ]);
    }


public function paid(): static
    {
        return $this->state([
            'payment_status' => 'paid',
        ]);
    }


public function partiallyPaid(): static
    {
        return $this->state([
            'payment_status' => 'partially_paid',
        ]);
    }
}
