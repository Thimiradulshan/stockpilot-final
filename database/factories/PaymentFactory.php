<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{

protected $model = Payment::class;


public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'payment_date' => now(),
            'amount' => 100.00,
            'payment_method' => fake()->randomElement([
                'cash',
                'card',
                'bank_transfer',
            ]),
            'reference' => fake()->optional()->bothify('REF-######'),
            'idempotency_key' => fake()->unique()->uuid(),
            'notes' => fake()->optional()->sentence(),
            'received_by' => User::factory()->state([
                'role' => UserRole::SALES,
            ]),
        ];
    }
}
