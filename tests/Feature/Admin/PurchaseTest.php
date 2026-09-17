<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_purchase_and_stock_is_increased(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'cost_price' => 100.00,
                'status' => 'active',
            ]);

        $payload = [
            'purchase_number' => 'PO-HTTP-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '25.00',
            'tax_amount' => '10.00',
            'notes' => 'HTTP purchase test',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_cost' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload);

        $response
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHas(
                'success',
                'Purchase created successfully.'
            );

        $purchase = Purchase::query()
            ->where('purchase_number', 'PO-HTTP-001')
            ->first();

        $this->assertNotNull($purchase);

        $this->assertSame($supplier->id, $purchase->supplier_id);
        $this->assertSame($admin->id, $purchase->created_by);
        $this->assertSame('completed', $purchase->status);
        $this->assertSame('500.00', $purchase->subtotal);
        $this->assertSame('25.00', $purchase->discount_amount);
        $this->assertSame('10.00', $purchase->tax_amount);
        $this->assertSame('485.00', $purchase->total_amount);
        $this->assertSame('HTTP purchase test', $purchase->notes);

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => '5.000',
            'unit_cost' => '100.00',
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'line_total' => '500.00',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'purchase',
            'quantity' => 5,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
        ]);

        $this->assertSame(
            '15.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_stock_user_can_create_purchase(): void
    {
        $stock = User::factory()->stock()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(20)
            ->create([
                'cost_price' => 250.00,
                'status' => 'active',
            ]);

        $payload = [
            'purchase_number' => 'PO-HTTP-002',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '3',
                    'unit_cost' => '250.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($stock)
            ->post(route('admin.purchases.store'), $payload)
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHas(
                'success',
                'Purchase created successfully.'
            );

        $purchase = Purchase::query()
            ->where('purchase_number', 'PO-HTTP-002')
            ->firstOrFail();

        $this->assertSame($stock->id, $purchase->created_by);

        $this->assertSame(
            '23.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_purchase_total_is_calculated_by_server(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'cost_price' => 50.00,
                'status' => 'active',
            ]);

        $payload = [
            'purchase_number' => 'PO-HTTP-003',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),

            'subtotal' => '1.00',
            'total_amount' => '1.00',

            'discount_amount' => '20.00',
            'tax_amount' => '5.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '10',
                    'unit_cost' => '50.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertRedirect(route('admin.purchases.index'));

        $purchase = Purchase::query()
            ->where('purchase_number', 'PO-HTTP-003')
            ->firstOrFail();

        $this->assertSame('500.00', $purchase->subtotal);
        $this->assertSame('20.00', $purchase->discount_amount);
        $this->assertSame('5.00', $purchase->tax_amount);
        $this->assertSame('485.00', $purchase->total_amount);
    }

    public function test_duplicate_purchase_number_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        Purchase::factory()->create([
            'purchase_number' => 'PO-DUPLICATE-001',
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
        ]);

        $payload = [
            'purchase_number' => 'PO-DUPLICATE-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '1',
                    'unit_cost' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors('purchase_number');

        $this->assertSame(
            1,
            Purchase::query()
                ->where('purchase_number', 'PO-DUPLICATE-001')
                ->count()
        );
    }

    public function test_invalid_purchase_data_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = [
            'supplier_id' => $supplier->id,
            'purchase_date' => 'not-a-date',
            'discount_amount' => '-1.00',
            'tax_amount' => '-2.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '0',
                    'unit_cost' => '-50.00',
                    'discount_amount' => '-1.00',
                    'tax_amount' => '-1.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors([
                'purchase_date',
                'discount_amount',
                'tax_amount',
                'items.0.quantity',
                'items.0.unit_cost',
                'items.0.discount_amount',
                'items.0.tax_amount',
            ]);

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_inactive_supplier_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->inactive()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = [
            'purchase_number' => 'PO-INACTIVE-SUPPLIER-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '1',
                    'unit_cost' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors('supplier_id');

        $this->assertDatabaseMissing('purchases', [
            'purchase_number' => 'PO-INACTIVE-SUPPLIER-001',
        ]);
    }

    public function test_inactive_product_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()->inactive()->create([
            'quantity' => 0,
        ]);

        $payload = [
            'purchase_number' => 'PO-INACTIVE-PRODUCT-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '2',
                    'unit_cost' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors('items.0.product_id');

        $this->assertDatabaseMissing('purchases', [
            'purchase_number' => 'PO-INACTIVE-PRODUCT-001',
        ]);

        $this->assertSame(
            '0.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_completed_purchase_can_be_cancelled_and_stock_is_reversed(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'cost_price' => 100.00,
                'status' => 'active',
            ]);

        $createPayload = [
            'purchase_number' => 'PO-CANCEL-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => 'Cancellation HTTP test',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_cost' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $createPayload)
            ->assertRedirect(route('admin.purchases.index'));

        $purchase = Purchase::query()
            ->where('purchase_number', 'PO-CANCEL-001')
            ->firstOrFail();

        $this->assertSame(
            '15.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );

        $this->actingAs($admin)
            ->post(route('admin.purchases.cancel', $purchase))
            ->assertRedirect(route('admin.purchases.index'))
            ->assertSessionHas(
                'success',
                'Purchase cancelled successfully.'
            );

        $purchase->refresh();

        $this->assertSame('cancelled', $purchase->status);

        $this->assertSame(
            '10.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'correction',
            'quantity' => -5,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
        ]);
    }

    public function test_cancelled_purchase_cannot_be_cancelled_again(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $purchase = Purchase::factory()->create([
            'supplier_id' => $supplier->id,
            'created_by' => $admin->id,
            'status' => 'cancelled',
        ]);

        $purchase->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_cost' => 100.00,
            'discount_amount' => 0.00,
            'tax_amount' => 0.00,
            'line_total' => 200.00,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.purchases.cancel', $purchase))
            ->assertForbidden();
    }

    public function test_per_line_tax_exceeding_the_line_gross_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = [
            'purchase_number' => 'PO-OVERTAXED-LINE-001',
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '5',
                    'unit_cost' => '200.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '1000000000.00',
                ],
            ],
        ];

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHas('error');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_future_purchase_date_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = $this->purchasePayload($product, $supplier->id, [
            'purchase_number' => 'PO-FUTURE-DATE-001',
            'purchase_date' => now()->addDay()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors('purchase_date');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_purchase_date_older_than_lookback_window_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
            ->create([
                'status' => 'active',
            ]);

        $payload = $this->purchasePayload($product, $supplier->id, [
            'purchase_number' => 'PO-OLD-DATE-001',
            'purchase_date' => now()
                ->subDays(
                    (int) config(
                        'stockpilot.document_date_lookback_days'
                    ) + 1
                )
                ->toDateString(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertSessionHasErrors('purchase_date');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_purchase_date_at_lookback_window_boundary_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()
            ->withQuantity(0)
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

        $payload = $this->purchasePayload($product, $supplier->id, [
            'purchase_number' => 'PO-BOUNDARY-DATE-001',
            'purchase_date' => $boundaryDate,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.purchases.store'), $payload)
            ->assertRedirect(route('admin.purchases.index'));

        $purchase = Purchase::query()
            ->where('purchase_number', 'PO-BOUNDARY-DATE-001')
            ->firstOrFail();

        $this->assertSame(
            $boundaryDate,
            $purchase->purchase_date->toDateString()
        );
    }


private function purchasePayload(
        Product $product,
        int $supplierId,
        array $overrides = [],
    ): array {
        return array_merge([
            'supplier_id' => $supplierId,
            'purchase_date' => now()->toDateString(),
            'discount_amount' => '0.00',
            'tax_amount' => '0.00',
            'notes' => null,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => '1',
                    'unit_cost' => '100.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
        ], $overrides);
    }
}
