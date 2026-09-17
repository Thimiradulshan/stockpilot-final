<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_movement_belongs_to_a_product(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'purchase',
            'quantity' => '10.000',
            'quantity_before' => '5.000',
            'quantity_after' => '15.000',
            'reason' => 'Initial purchase',
            'created_by' => $user->id,
        ]);

        $this->assertTrue(
            $movement->product->is($product)
        );
    }

    public function test_stock_movement_belongs_to_the_user_who_created_it(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'adjustment',
            'quantity' => '5.000',
            'quantity_before' => '10.000',
            'quantity_after' => '15.000',
            'reason' => 'Stock correction',
            'created_by' => $user->id,
        ]);

        $this->assertTrue(
            $movement->createdBy->is($user)
        );
    }

    public function test_stock_quantities_are_cast_to_three_decimal_places(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'purchase',
            'quantity' => '12.345',
            'quantity_before' => '7.125',
            'quantity_after' => '19.470',
            'created_by' => $user->id,
        ]);

        $movement->refresh();

        $this->assertSame('12.345', (string) $movement->quantity);
        $this->assertSame('7.125', (string) $movement->quantity_before);
        $this->assertSame('19.470', (string) $movement->quantity_after);
    }

    public function test_stock_movement_accepts_reference_information(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => '-2.000',
            'quantity_before' => '10.000',
            'quantity_after' => '8.000',
            'reference_type' => 'invoice',
            'reference_id' => 123,
            'reason' => 'Customer sale',
            'notes' => 'Invoice stock deduction',
            'created_by' => $user->id,
        ]);

        $movement->refresh();

        $this->assertSame('invoice', $movement->reference_type);
        $this->assertSame(123, $movement->reference_id);
        $this->assertSame('Customer sale', $movement->reason);
        $this->assertSame('Invoice stock deduction', $movement->notes);
    }

    public function test_negative_quantity_can_represent_stock_removal(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => '-3.500',
            'quantity_before' => '10.000',
            'quantity_after' => '6.500',
            'created_by' => $user->id,
        ]);

        $movement->refresh();

        $this->assertSame('-3.500', (string) $movement->quantity);
        $this->assertSame('6.500', (string) $movement->quantity_after);
    }

    public function test_stock_movement_cannot_be_updated(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'purchase',
            'quantity' => '10.000',
            'quantity_before' => '5.000',
            'quantity_after' => '15.000',
            'reason' => 'Original reason',
            'created_by' => $user->id,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Stock movements are immutable and cannot be updated.'
        );

        $movement->update([
            'reason' => 'Tampered reason',
        ]);
    }

    public function test_stock_movement_cannot_be_deleted(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $movement = StockMovement::query()->create([
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => '-2.000',
            'quantity_before' => '10.000',
            'quantity_after' => '8.000',
            'reason' => 'Original sale',
            'created_by' => $user->id,
        ]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Stock movements are immutable and cannot be deleted.'
        );

        $movement->delete();
    }

    public function test_mysql_database_rejects_an_invalid_movement_type(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped(
                'The movement type CHECK constraint is MySQL-specific.'
            );
        }

        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $user = User::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('stock_movements')->insert([
            'product_id' => $product->id,
            'movement_type' => 'hacked',
            'quantity' => '1.000',
            'quantity_before' => '0.000',
            'quantity_after' => '1.000',
            'created_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
