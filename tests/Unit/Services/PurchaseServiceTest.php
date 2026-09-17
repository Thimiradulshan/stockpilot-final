<?php

namespace Tests\Unit\Services;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\DocumentNumberService;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Tests\TestCase;

class PurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseService $purchaseService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseService = new PurchaseService(
            new StockService,
            new DocumentNumberService,
        );
    }

    public function test_purchase_can_be_created_and_increases_stock(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $productA = $this->createProduct(
            name: 'Product A',
            costPrice: '50.00',
            quantity: '10.000',
        );

        $productB = $this->createProduct(
            name: 'Product B',
            costPrice: '100.00',
            quantity: '5.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-TEST-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $productA->id,
                    'quantity' => '5.000',
                    'unit_cost' => '50.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
                [
                    'product_id' => $productB->id,
                    'quantity' => '2.000',
                    'unit_cost' => '100.00',
                    'discount_amount' => '10.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );

        $this->assertInstanceOf(
            Purchase::class,
            $purchase
        );

        $this->assertSame(
            'PUR-TEST-001',
            $purchase->purchase_number
        );

        $this->assertSame(
            $supplier->id,
            $purchase->supplier_id
        );

        $this->assertSame(
            '440.00',
            (string) $purchase->subtotal
        );

        $this->assertSame(
            '0.00',
            (string) $purchase->discount_amount
        );

        $this->assertSame(
            '0.00',
            (string) $purchase->tax_amount
        );

        $this->assertSame(
            '440.00',
            (string) $purchase->total_amount
        );

        $this->assertSame(
            '15.000',
            (string) $productA->fresh()->quantity
        );

        $this->assertSame(
            '7.000',
            (string) $productB->fresh()->quantity
        );

        $this->assertCount(
            2,
            PurchaseItem::query()
                ->where('purchase_id', $purchase->id)
                ->get()
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productA->id,
            'movement_type' => StockMovementType::PURCHASE->value,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'created_by' => $user->id,
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productB->id,
            'movement_type' => StockMovementType::PURCHASE->value,
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'created_by' => $user->id,
        ]);
    }

    public function test_purchase_totals_are_calculated_by_the_server(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Server Calculated Product',
            costPrice: '20.00',
            quantity: '0.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-TOTAL-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '10.000',
                    'unit_cost' => '20.00',
                    'discount_amount' => '25.00',
                    'tax_amount' => '10.00',
                ],
            ],
            discountAmount: '15.00',
            taxAmount: '5.00',
            actor: $user,
        );

        $this->assertSame(
            '185.00',
            (string) $purchase->subtotal
        );

        $this->assertSame(
            '15.00',
            (string) $purchase->discount_amount
        );

        $this->assertSame(
            '5.00',
            (string) $purchase->tax_amount
        );

        $this->assertSame(
            '175.00',
            (string) $purchase->total_amount
        );
    }

    public function test_duplicate_purchase_number_is_rejected_gracefully(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Duplicate Number Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-DUP-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '1.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'A purchase with this number already exists.'
        );

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-DUP-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '2.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_zero_quantity_is_rejected(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Zero Quantity Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->expectException(InvalidArgumentException::class);

        $this->expectExceptionMessage(
            'Purchase item quantity must be greater than zero.'
        );

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-INVALID-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '0.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_negative_quantity_is_rejected(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Negative Quantity Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->expectException(InvalidArgumentException::class);

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-INVALID-002',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '-1.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_more_than_three_quantity_decimals_are_rejected(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Decimal Quantity Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->expectException(InvalidArgumentException::class);

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-INVALID-003',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '1.1234',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_negative_unit_cost_is_rejected(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Negative Cost Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->expectException(InvalidArgumentException::class);

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-INVALID-004',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '1.000',
                    'unit_cost' => '-10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_inactive_supplier_is_rejected(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->inactive()->create();

        $product = $this->createProduct(
            name: 'Inactive Supplier Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'Inactive suppliers cannot be used for purchases.'
        );

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-SUPPLIER-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '1.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_inactive_product_is_rejected(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Inactive Product',
            costPrice: '10.00',
            quantity: '0.000',
            status: 'inactive',
        );

        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'Inactive products cannot be purchased.'
        );

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-PRODUCT-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '1.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_inactive_user_cannot_create_purchase(): void
    {
        $user = User::factory()->stock()->inactive()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Inactive User Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $this->expectException(LogicException::class);

        $this->expectExceptionMessage(
            'Inactive users cannot create purchases.'
        );

        $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-USER-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '1.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );
    }

    public function test_purchase_and_stock_are_atomic_when_stock_update_fails(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Atomic Purchase Product',
            costPrice: '10.00',
            quantity: '5.000',
        );

        $failingStockService = new class extends StockService
        {
            public function increase(
                Product $product,
                string|int $quantity,
                User $actor,
                StockMovementType $movementType = StockMovementType::PURCHASE,
                ?string $referenceType = null,
                ?int $referenceId = null,
                ?string $reason = null,
                ?string $notes = null,
            ): StockMovement {
                throw new RuntimeException(
                    'Simulated stock update failure.'
                );
            }
        };

        $failingPurchaseService = new PurchaseService(
            $failingStockService,
            new DocumentNumberService,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Simulated stock update failure.'
        );

        try {
            $failingPurchaseService->create(
                supplier: $supplier,
                purchaseNumber: 'PUR-ATOMIC-001',
                purchaseDate: '2026-09-10',
                items: [
                    [
                        'product_id' => $product->id,
                        'quantity' => '10.000',
                        'unit_cost' => '10.00',
                        'discount_amount' => '0.00',
                        'tax_amount' => '0.00',
                    ],
                ],
                discountAmount: '0.00',
                taxAmount: '0.00',
                actor: $user,
            );
        } finally {
            $this->assertDatabaseCount('purchases', 0);
            $this->assertDatabaseCount('purchase_items', 0);
            $this->assertDatabaseCount('stock_movements', 0);

            $this->assertSame(
                '5.000',
                (string) $product->fresh()->quantity
            );
        }
    }

    public function test_purchase_items_record_calculated_line_totals(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Line Total Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-LINE-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '4.000',
                    'unit_cost' => '25.00',
                    'discount_amount' => '10.00',
                    'tax_amount' => '5.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );

        $item = PurchaseItem::query()
            ->where('purchase_id', $purchase->id)
            ->firstOrFail();

        $this->assertSame(
            '95.00',
            (string) $item->line_total
        );

        $this->assertSame(
            '25.00',
            (string) $item->unit_cost
        );

        $this->assertSame(
            '4.000',
            (string) $item->quantity
        );

        $movement = StockMovement::query()
            ->where('product_id', $product->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame(
            StockMovementType::PURCHASE,
            $movement->movement_type
        );
    }

    public function test_completed_purchase_can_be_cancelled_and_reverses_stock(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Cancellable Product',
            costPrice: '25.00',
            quantity: '10.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-CANCEL-001',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '5.000',
                    'unit_cost' => '25.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );

        $cancelledPurchase = $this->purchaseService->cancel(
            purchase: $purchase,
            actor: $user,
        );

        $this->assertSame(
            'cancelled',
            $cancelledPurchase->status
        );

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::CORRECTION->value,
            'quantity' => '-5.000',
            'quantity_before' => '15.000',
            'quantity_after' => '10.000',
            'reference_type' => 'purchase',
            'reference_id' => $purchase->id,
            'created_by' => $user->id,
        ]);
    }

    public function test_cancelled_purchase_cannot_be_cancelled_again(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Already Cancelled Product',
            costPrice: '25.00',
            quantity: '0.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-CANCEL-002',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '5.000',
                    'unit_cost' => '25.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );

        $this->purchaseService->cancel(
            purchase: $purchase,
            actor: $user,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'The purchase has already been cancelled.'
        );

        $this->purchaseService->cancel(
            purchase: $purchase->fresh(),
            actor: $user,
        );
    }

    public function test_purchase_cancellation_is_rejected_when_stock_is_insufficient(): void
    {
        $user = User::factory()->stock()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Insufficient Cancellation Stock Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-CANCEL-003',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '100.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $user,
        );

        $stockService = new StockService;

        $stockService->decrease(
            product: $product->fresh(),
            quantity: '80.000',
            actor: $user,
            movementType: StockMovementType::SALE,
            referenceType: 'test',
            referenceId: $purchase->id,
            reason: 'Simulated subsequent sale.',
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Purchase cancellation would make stock negative for product.'
        );

        $this->purchaseService->cancel(
            purchase: $purchase->fresh(),
            actor: $user,
        );

        $this->assertSame(
            '20.000',
            (string) $product->fresh()->quantity
        );

        $this->assertSame(
            'completed',
            $purchase->fresh()->status
        );
    }

    public function test_inactive_user_cannot_cancel_purchase(): void
    {
        $creator = User::factory()->stock()->create();
        $inactiveUser = User::factory()->stock()->inactive()->create();
        $supplier = Supplier::factory()->create();

        $product = $this->createProduct(
            name: 'Inactive Cancellation User Product',
            costPrice: '10.00',
            quantity: '0.000',
        );

        $purchase = $this->purchaseService->create(
            supplier: $supplier,
            purchaseNumber: 'PUR-CANCEL-004',
            purchaseDate: '2026-09-10',
            items: [
                [
                    'product_id' => $product->id,
                    'quantity' => '5.000',
                    'unit_cost' => '10.00',
                    'discount_amount' => '0.00',
                    'tax_amount' => '0.00',
                ],
            ],
            discountAmount: '0.00',
            taxAmount: '0.00',
            actor: $creator,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Inactive users cannot cancel purchases.'
        );

        $this->purchaseService->cancel(
            purchase: $purchase,
            actor: $inactiveUser,
        );
    }

    private function createProduct(
        string $name,
        string $costPrice,
        string $quantity,
        string $status = 'active',
    ): Product {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'name' => $name,
            'cost_price' => $costPrice,
            'selling_price' => $costPrice,
            'quantity' => $quantity,
            'reorder_level' => '0.000',
            'status' => $status,
        ]);
    }
}
