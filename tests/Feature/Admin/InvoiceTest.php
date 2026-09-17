<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_invoice_and_stock_is_decreased(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-HTTP-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '25.00',
            'tax_rate' => '5.00',
            'notes' => 'HTTP invoice test',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_price' => '200.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload);

        $response
            ->assertRedirect(route('admin.sales.index'))
            ->assertSessionHas(
                'success',
                'Invoice created successfully.'
            );

        $invoice = Invoice::query()
            ->where('invoice_number', 'INV-HTTP-001')
            ->first();

        $this->assertNotNull($invoice);

        $this->assertSame($customer->id, $invoice->customer_id);
        $this->assertSame($admin->id, $invoice->created_by);
        $this->assertSame('completed', $invoice->status);
        $this->assertSame('unpaid', $invoice->payment_status);
        $this->assertSame('1000.00', $invoice->subtotal);
        $this->assertSame('25.00', $invoice->discount_amount);
        $this->assertSame('5.00', $invoice->tax_rate);
        $this->assertSame('50.00', $invoice->tax_amount);
        $this->assertSame('1025.00', $invoice->total_amount);
        $this->assertSame('HTTP invoice test', $invoice->notes);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => '5.000',
            'unit_price' => '200.00',
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'line_total' => '1000.00',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => -5,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
        ]);

        $this->assertSame(
            '5.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_sales_user_can_create_invoice(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(20)
            ->create([
                'selling_price' => 250.00,
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-HTTP-002',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '3',
                    'unit_price' => '250.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($sales)
            ->post(route('admin.sales.store'), $payload)
            ->assertRedirect(route('admin.sales.index'))
            ->assertSessionHas(
                'success',
                'Invoice created successfully.'
            );

        $invoice = Invoice::query()
            ->where('invoice_number', 'INV-HTTP-002')
            ->firstOrFail();

        $this->assertSame($sales->id, $invoice->created_by);

        $this->assertSame(
            '17.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_invoice_total_is_calculated_by_server(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(20)
            ->create([
                'selling_price' => 50.00,
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-HTTP-003',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),

            'subtotal' => '1.00',
            'tax_amount' => '1.00',
            'total_amount' => '1.00',

            'discount_amount' => '20.00',
            'tax_rate' => '10.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '10',
                    'unit_price' => '50.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertRedirect(route('admin.sales.index'));

        $invoice = Invoice::query()
            ->where('invoice_number', 'INV-HTTP-003')
            ->firstOrFail();

        $this->assertSame('500.00', $invoice->subtotal);
        $this->assertSame('20.00', $invoice->discount_amount);
        $this->assertSame('10.00', $invoice->tax_rate);
        $this->assertSame('50.00', $invoice->tax_amount);
        $this->assertSame('530.00', $invoice->total_amount);
    }

    public function test_duplicate_invoice_number_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        Invoice::factory()->create([
            'invoice_number' => 'INV-DUPLICATE-001',
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
        ]);

        $payload = [
            'invoice_number' => 'INV-DUPLICATE-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '1',
                    'unit_price' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHasErrors('invoice_number');

        $this->assertSame(
            1,
            Invoice::query()
                ->where('invoice_number', 'INV-DUPLICATE-001')
                ->count()
        );
    }

    public function test_invalid_invoice_data_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = [
            'customer_id' => $customer->id,
            'invoice_date' => 'not-a-date',
            'discount_amount' => '-1.00',
            'tax_rate' => '-5.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '0',
                    'unit_price' => '-50.00',
                    'discount_amount' => '-1.00',
                    'tax_amount' => '-1.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHasErrors([
                'invoice_date',
                'discount_amount',
                'tax_rate',
                'items.0.quantity',
                'items.0.unit_price',
                'items.0.discount_amount',
                'items.0.tax_amount',
            ]);

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_inactive_customer_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->inactive()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-INACTIVE-CUSTOMER-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '1',
                    'unit_price' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHasErrors('customer_id');

        $this->assertDatabaseMissing('invoices', [
            'invoice_number' => 'INV-INACTIVE-CUSTOMER-001',
        ]);
    }

    public function test_inactive_product_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()->inactive()->create([
            'quantity' => 0,
        ]);

        $payload = [
            'invoice_number' => 'INV-INACTIVE-PRODUCT-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '2',
                    'unit_price' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHasErrors('items.0.product_id');

        $this->assertDatabaseMissing('invoices', [
            'invoice_number' => 'INV-INACTIVE-PRODUCT-001',
        ]);

        $this->assertSame(
            '0.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_insufficient_stock_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(3)
            ->create([
                'selling_price' => 100.00,
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-NO-STOCK-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_price' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Stock quantity cannot become negative.'
            );

        $this->assertDatabaseMissing('invoices', [
            'invoice_number' => 'INV-NO-STOCK-001',
        ]);

        $this->assertSame(
            '3.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_completed_invoice_can_be_voided_and_stock_is_restored(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 100.00,
                'status' => 'active',
            ]);

        $createPayload = [
            'invoice_number' => 'INV-VOID-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => 'Void HTTP test',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_price' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $createPayload)
            ->assertRedirect(route('admin.sales.index'));

        $invoice = Invoice::query()
            ->where('invoice_number', 'INV-VOID-001')
            ->firstOrFail();

        $this->assertSame(
            '5.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );

        $this->actingAs($admin)
            ->post(route('admin.sales.void', $invoice))
            ->assertRedirect(route('admin.sales.index'))
            ->assertSessionHas(
                'success',
                'Invoice voided successfully.'
            );

        $invoice->refresh();

        $this->assertSame('voided', $invoice->status);

        $this->assertSame(
            '10.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'return',
            'quantity' => 5,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
        ]);
    }

    public function test_voided_invoice_cannot_be_voided_again(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'status' => 'voided',
        ]);

        $invoice->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'line_total' => 200.00,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.void', $invoice))
            ->assertForbidden();
    }

    public function test_paid_invoice_cannot_be_voided(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'status' => 'completed',
            'payment_status' => 'paid',
            'total_amount' => 1000.00,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 1000.00,
            'received_by' => $admin->id,
        ]);

        $this->assertNotNull($payment);

        $this->actingAs($admin)
            ->post(route('admin.sales.void', $invoice))
            ->assertForbidden();
    }

    public function test_payment_can_be_recorded_against_completed_invoice(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'total_amount' => 1000.00,
        ]);

        $payload = [
            'amount' => '400.00',
            'payment_method' => 'cash',
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.payments.store', $invoice), $payload)
            ->assertRedirect(route('admin.sales.index'))
            ->assertSessionHas(
                'success',
                'Payment recorded successfully.'
            );

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => '400.00',
            'payment_method' => 'cash',
            'received_by' => $admin->id,
        ]);

        $invoice->refresh();

        $this->assertSame('partially_paid', $invoice->payment_status);

        $this->actingAs($admin)
            ->post(route('admin.sales.payments.store', $invoice), [
                'amount' => '600.00',
                'payment_method' => 'bank_transfer',
            ])
            ->assertRedirect(route('admin.sales.index'));

        $invoice->refresh();

        $this->assertSame('paid', $invoice->payment_status);
    }

    public function test_payment_over_balance_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'total_amount' => 1000.00,
        ]);

        $payload = [
            'amount' => '1500.00',
            'payment_method' => 'cash',
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.payments.store', $invoice), $payload)
            ->assertRedirect()
            ->assertSessionHas(
                'error',
                'Payment amount exceeds the outstanding invoice balance.'
            );

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_voided_invoice_cannot_receive_payments(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'status' => 'voided',
            'payment_status' => 'unpaid',
            'total_amount' => 1000.00,
        ]);

        $payload = [
            'amount' => '100.00',
            'payment_method' => 'cash',
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.payments.store', $invoice), $payload)
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_stock_user_cannot_access_sales(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('admin.sales.index'))
            ->assertForbidden();
    }

    public function test_header_tax_is_computed_on_tax_exclusive_subtotal(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-NO-TAX-ON-TAX-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '10.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_price' => '200.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '20.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertRedirect(route('admin.sales.index'));

        $invoice = Invoice::query()
            ->where('invoice_number', 'INV-NO-TAX-ON-TAX-001')
            ->firstOrFail();

        $this->assertSame('1000.00', $invoice->subtotal);
        $this->assertSame('100.00', $invoice->tax_amount);
        $this->assertSame('1100.00', $invoice->total_amount);
    }

    public function test_per_line_tax_exceeding_the_line_gross_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        $payload = [
            'invoice_number' => 'INV-OVERTAXED-LINE-001',
            'customer_id' => $customer->id,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_price' => '200.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '1000000000.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHas('error');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_future_invoice_date_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'status' => 'active',
            ]);

        $payload = $this->invoicePayload($product, $customer->id, [
            'invoice_number' => 'INV-FUTURE-DATE-001',
            'invoice_date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHasErrors('invoice_date');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_invoice_date_older_than_lookback_window_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'status' => 'active',
            ]);

        $payload = $this->invoicePayload($product, $customer->id, [
            'invoice_number' => 'INV-OLD-DATE-001',
            'invoice_date' => now()
                ->subDays(
                    (int) config(
                        'stockpilot.document_date_lookback_days'
                    ) + 1
                )
                ->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertSessionHasErrors('invoice_date');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_invoice_date_at_lookback_window_boundary_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'status' => 'active',
            ]);

        $boundaryDate = now()
            ->subDays(
                (int) config(
                    'stockpilot.document_date_lookback_days'
                )
            )
            ->toDateString();

        $payload = $this->invoicePayload($product, $customer->id, [
            'invoice_number' => 'INV-BOUNDARY-DATE-001',
            'invoice_date' => $boundaryDate,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.store'), $payload)
            ->assertRedirect(route('admin.sales.index'));

        $invoice = Invoice::query()
            ->where('invoice_number', 'INV-BOUNDARY-DATE-001')
            ->firstOrFail();

        $this->assertSame(
            $boundaryDate,
            $invoice->invoice_date->toDateString()
        );
    }


private function invoicePayload(
        Product $product,
        int $customerId,
        array $overrides = [],
    ): array {
        return array_merge([
            'customer_id' => $customerId,
            'invoice_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '1',
                    'unit_price' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ], $overrides);
    }

    public function test_invoice_with_recorded_payment_cannot_be_voided_even_if_status_drifts(): void
    {
        $admin = User::factory()->admin()->create();

        $customer = Customer::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $admin->id,
            'status' => 'completed',
            'payment_status' => 'unpaid',
            'total_amount' => '1000.00',
        ]);

        $invoice->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'line_total' => 200.00,
        ]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => '1000.00',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sales.void', $invoice))
            ->assertSessionHas('error');

        $this->assertSame(
            'completed',
            $invoice->fresh()->status
        );
    }
}
