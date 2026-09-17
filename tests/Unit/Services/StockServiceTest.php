<?php

namespace Tests\Unit\Services;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\StockService;
use Brick\Math\BigDecimal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = new StockService;
    }

    public function test_increase_adds_stock_and_creates_a_positive_movement(): void
    {
        $product = $this->createProduct(quantity: '10.000');
        $user = User::factory()->create();

        $movement = $this->stockService->increase(
            product: $product,
            quantity: '5.250',
            actor: $user,
        );

        $product->refresh();

        $this->assertSame('15.250', (string) $product->quantity);

        $this->assertSame(
            StockMovementType::PURCHASE,
            $movement->movement_type
        );

        $this->assertSame(
            '5.250',
            (string) $movement->quantity
        );

        $this->assertSame(
            '10.000',
            (string) $movement->quantity_before
        );

        $this->assertSame(
            '15.250',
            (string) $movement->quantity_after
        );

        $this->assertSame(
            $user->id,
            $movement->created_by
        );
    }

    public function test_decrease_removes_stock_and_creates_a_negative_movement(): void
    {
        $product = $this->createProduct(quantity: '10.000');
        $user = User::factory()->create();

        $movement = $this->stockService->decrease(
            product: $product,
            quantity: '3.500',
            actor: $user,
        );

        $product->refresh();

        $this->assertSame('6.500', (string) $product->quantity);

        $this->assertSame(
            StockMovementType::SALE,
            $movement->movement_type
        );

        $this->assertSame(
            '-3.500',
            (string) $movement->quantity
        );

        $this->assertSame(
            '10.000',
            (string) $movement->quantity_before
        );

        $this->assertSame(
            '6.500',
            (string) $movement->quantity_after
        );
    }

    public function test_adjustment_can_add_stock(): void
    {
        $product = $this->createProduct(quantity: '10.000');
        $user = User::factory()->create();

        $movement = $this->stockService->adjust(
            product: $product,
            signedQuantity: '2.125',
            actor: $user,
        );

        $product->refresh();

        $this->assertSame('12.125', (string) $product->quantity);
        $this->assertSame('2.125', (string) $movement->quantity);
        $this->assertSame(
            StockMovementType::ADJUSTMENT,
            $movement->movement_type
        );
    }

    public function test_adjustment_can_remove_stock(): void
    {
        $product = $this->createProduct(quantity: '10.000');
        $user = User::factory()->create();

        $movement = $this->stockService->adjust(
            product: $product,
            signedQuantity: '-2.125',
            actor: $user,
        );

        $product->refresh();

        $this->assertSame('7.875', (string) $product->quantity);
        $this->assertSame('-2.125', (string) $movement->quantity);
        $this->assertSame(
            StockMovementType::ADJUSTMENT,
            $movement->movement_type
        );
    }

    public function test_stock_cannot_become_negative(): void
    {
        $product = $this->createProduct(quantity: '5.000');
        $user = User::factory()->create();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Stock quantity cannot become negative.'
        );

        $this->stockService->decrease(
            product: $product,
            quantity: '5.001',
            actor: $user,
        );

        $product->refresh();

        $this->assertSame('5.000', (string) $product->quantity);

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_zero_quantity_is_rejected(): void
    {
        $product = $this->createProduct(quantity: '5.000');
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->increase(
            product: $product,
            quantity: '0',
            actor: $user,
        );
    }

    public function test_negative_quantity_is_rejected_by_increase(): void
    {
        $product = $this->createProduct(quantity: '5.000');
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->increase(
            product: $product,
            quantity: '-1.000',
            actor: $user,
        );
    }

    public function test_negative_quantity_is_rejected_by_decrease(): void
    {
        $product = $this->createProduct(quantity: '5.000');
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->decrease(
            product: $product,
            quantity: '-1.000',
            actor: $user,
        );
    }

    public function test_adjustment_cannot_be_zero(): void
    {
        $product = $this->createProduct(quantity: '5.000');
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Stock adjustment quantity must not be zero.'
        );

        $this->stockService->adjust(
            product: $product,
            signedQuantity: '0',
            actor: $user,
        );
    }

    public function test_more_than_three_decimal_places_are_rejected(): void
    {
        $product = $this->createProduct(quantity: '5.000');
        $user = User::factory()->create();

        $this->expectException(InvalidArgumentException::class);

        $this->stockService->increase(
            product: $product,
            quantity: '1.1234',
            actor: $user,
        );
    }

    public function test_movement_reference_information_is_recorded(): void
    {
        $product = $this->createProduct(quantity: '10.000');
        $user = User::factory()->create();

        $movement = $this->stockService->increase(
            product: $product,
            quantity: '4.000',
            actor: $user,
            movementType: StockMovementType::PURCHASE,
            referenceType: 'purchase',
            referenceId: 25,
            reason: 'Supplier delivery',
            notes: 'Received in good condition.',
        );

        $movement->refresh();

        $this->assertSame('purchase', $movement->reference_type);
        $this->assertSame(25, $movement->reference_id);
        $this->assertSame('Supplier delivery', $movement->reason);
        $this->assertSame(
            'Received in good condition.',
            $movement->notes
        );
        $this->assertSame(
            StockMovementType::PURCHASE,
            $movement->movement_type
        );
    }

    public function test_inactive_product_cannot_be_used_for_stock_operations(): void
    {
        $product = $this->createProduct(quantity: '10.000');

        $product->update([
            'status' => 'inactive',
        ]);

        $user = User::factory()->create();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Inactive products cannot be used in stock operations.'
        );

        $this->stockService->increase(
            product: $product,
            quantity: '5.000',
            actor: $user,
        );

        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );
    }

    public function test_inactive_user_cannot_perform_stock_operations(): void
    {
        $product = $this->createProduct(quantity: '10.000');

        $user = User::factory()->inactive()->create();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Inactive users cannot perform stock operations.'
        );

        $this->stockService->increase(
            product: $product,
            quantity: '5.000',
            actor: $user,
        );

        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );
    }

    public function test_product_quantity_and_movement_are_atomic(): void
    {
        $product = $this->createProduct(quantity: '10.000');
        $user = User::factory()->create();

        $failingService = new class extends StockService
        {
            protected function persistMovement(
                Product $product,
                StockMovementType $movementType,
                BigDecimal $quantity,
                BigDecimal $quantityBefore,
                BigDecimal $quantityAfter,
                User $actor,
                ?string $referenceType,
                ?int $referenceId,
                ?string $reason,
                ?string $notes,
            ): StockMovement {
                throw new \RuntimeException(
                    'Simulated movement ledger failure.'
                );
            }
        };

        try {
            $failingService->increase(
                product: $product,
                quantity: '5.000',
                actor: $user,
            );

            $this->fail(
                'The simulated movement failure was not thrown.'
            );
        } catch (\RuntimeException $exception) {
            $this->assertSame(
                'Simulated movement ledger failure.',
                $exception->getMessage()
            );
        }

        $product->refresh();

        $this->assertSame('10.000', (string) $product->quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    private function createProduct(string $quantity): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => $quantity,
            'status' => 'active',
        ]);
    }
}
