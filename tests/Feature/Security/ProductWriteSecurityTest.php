<?php

namespace Tests\Feature\Security;

use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWriteSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_a_product(): void
    {
        $category = Category::factory()->create();

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'SKU-TEST-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-TEST-001',
        ]);
    }

    public function test_sales_user_cannot_create_a_product(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::SALES->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'SKU-SALES-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-SALES-001',
        ]);
    }

    public function test_stock_user_can_create_a_valid_product(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Valid Product',
            'sku' => 'SKU-STOCK-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'category_id' => $category->id,
            'name' => 'Valid Product',
            'sku' => 'SKU-STOCK-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5.000',
            'quantity' => '0.000',
        ]);
    }

    public function test_admin_can_create_a_valid_product(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::ADMIN->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Admin Product',
            'sku' => 'SKU-ADMIN-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('products', [
            'sku' => 'SKU-ADMIN-001',
            'quantity' => '0.000',
        ]);
    }

    public function test_quantity_cannot_be_supplied_during_product_creation(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Quantity Attack',
            'sku' => 'SKU-QTY-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
            'quantity' => '9999',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $product = Product::query()
            ->where('sku', 'SKU-QTY-001')
            ->firstOrFail();

        $this->assertSame('0.000', (string) $product->quantity);
    }

    public function test_status_cannot_be_supplied_during_product_creation(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Status Attack',
            'sku' => 'SKU-STATUS-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
            'status' => 'inactive',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $product = Product::query()
            ->where('sku', 'SKU-STATUS-001')
            ->firstOrFail();

        $this->assertSame('active', $product->status);
    }

    public function test_negative_cost_price_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Negative Cost',
            'sku' => 'SKU-NEG-COST-001',
            'cost_price' => '-1.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasErrors('cost_price');

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-NEG-COST-001',
        ]);
    }

    public function test_negative_selling_price_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Negative Selling',
            'sku' => 'SKU-NEG-SELL-001',
            'cost_price' => '100.00',
            'selling_price' => '-1.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasErrors('selling_price');

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-NEG-SELL-001',
        ]);
    }

    public function test_negative_reorder_level_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Negative Reorder',
            'sku' => 'SKU-NEG-REORDER-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '-5',
        ]);

        $response->assertSessionHasErrors('reorder_level');

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-NEG-REORDER-001',
        ]);
    }

    public function test_invalid_category_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => 999999,
            'name' => 'Invalid Category',
            'sku' => 'SKU-CATEGORY-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-CATEGORY-001',
        ]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'sku' => 'SKU-DUPLICATE-001',
        ]);

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Duplicate SKU',
            'sku' => 'SKU-DUPLICATE-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_quantity_cannot_be_changed_during_product_update(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch("/admin/products/{$product->id}", [
            'category_id' => $category->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
            'quantity' => '9999',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );
    }

    public function test_status_cannot_be_changed_during_product_update(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'status' => 'active',
        ]);

        $this->actingAs($user);

        $response = $this->patch("/admin/products/{$product->id}", [
            'category_id' => $category->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
            'status' => 'inactive',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertSame(
            'active',
            $product->fresh()->status
        );
    }

    public function test_inactive_category_cannot_be_used_for_new_product(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $inactiveCategory = Category::factory()->inactive()->create();

        $this->actingAs($user);

        $response = $this->post('/admin/products', [
            'category_id' => $inactiveCategory->id,
            'name' => 'Inactive Category Product',
            'sku' => 'SKU-INACTIVE-CAT-001',
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $response->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('products', [
            'sku' => 'SKU-INACTIVE-CAT-001',
        ]);
    }

    public function test_existing_product_may_keep_its_current_inactive_category_but_cannot_switch_to_another_inactive_category(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::STOCK->value,
        ]);

        $currentCategory = Category::factory()->create();
        $newInactiveCategory = Category::factory()->inactive()->create();

        $product = Product::factory()->create([
            'category_id' => $currentCategory->id,
        ]);

        $currentCategory->update([
            'status' => 'inactive',
        ]);

        $this->actingAs($user);

        $keepCurrentResponse = $this->patch("/admin/products/{$product->id}", [
            'category_id' => $currentCategory->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $keepCurrentResponse->assertSessionHasNoErrors();
        $keepCurrentResponse->assertRedirect();

        $switchResponse = $this->patch("/admin/products/{$product->id}", [
            'category_id' => $newInactiveCategory->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'cost_price' => '100.00',
            'selling_price' => '150.00',
            'reorder_level' => '5',
        ]);

        $switchResponse->assertSessionHasErrors('category_id');

        $this->assertSame(
            $currentCategory->id,
            $product->fresh()->category_id
        );
    }

    public function test_guest_cannot_adjust_stock(): void
    {
        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '5.000',
                'reason' => 'Manual correction',
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_sales_user_cannot_adjust_stock(): void
    {
        $user = User::factory()->sales()->create();

        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '5.000',
                'reason' => 'Unauthorized adjustment',
            ]
        );

        $response->assertForbidden();

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_inactive_stock_user_is_logged_out_when_adjusting_stock(): void
    {
        $user = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '5.000',
                'reason' => 'Inactive user adjustment',
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_stock_user_can_adjust_stock(): void
    {
        $user = User::factory()->stock()->create();

        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '5.250',
                'reason' => 'Opening stock correction',
                'notes' => 'Verified against physical count.',
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.products.index'));

        $this->assertSame(
            '15.250',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::ADJUSTMENT->value,
            'quantity' => '5.250',
            'quantity_before' => '10.000',
            'quantity_after' => '15.250',
            'reason' => 'Opening stock correction',
            'notes' => 'Verified against physical count.',
            'created_by' => $user->id,
        ]);
    }

    public function test_admin_can_adjust_stock(): void
    {
        $user = User::factory()->admin()->create();

        $product = Product::factory()->create([
            'quantity' => '20.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '-3.500',
                'reason' => 'Physical count correction',
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.products.index'));

        $this->assertSame(
            '16.500',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => StockMovementType::ADJUSTMENT->value,
            'quantity' => '-3.500',
            'quantity_before' => '20.000',
            'quantity_after' => '16.500',
            'reason' => 'Physical count correction',
            'created_by' => $user->id,
        ]);
    }

    public function test_zero_stock_adjustment_is_rejected(): void
    {
        $user = User::factory()->stock()->create();

        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        foreach ([
            '0',
            '0.0',
            '0.00',
            '0.000',
            '-0',
            '-0.0',
            '-0.00',
            '-0.000',
        ] as $zeroValue) {
            $response = $this->patch(
                "/admin/products/{$product->id}/stock",
                [
                    'signed_quantity' => $zeroValue,
                    'reason' => 'Zero adjustment',
                ]
            );

            $response->assertSessionHasErrors('signed_quantity');
        }

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_stock_adjustment_with_more_than_three_decimal_places_is_rejected(): void
    {
        $user = User::factory()->stock()->create();

        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '1.1234',
                'reason' => 'Precision attack',
            ]
        );

        $response->assertSessionHasErrors('signed_quantity');

        $this->assertSame(
            '10.000',
            (string) $product->fresh()->quantity
        );

        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_movement_type_cannot_be_supplied_to_stock_adjustment_endpoint(): void
    {
        $user = User::factory()->stock()->create();

        $product = Product::factory()->create([
            'quantity' => '10.000',
        ]);

        $this->actingAs($user);

        $response = $this->patch(
            "/admin/products/{$product->id}/stock",
            [
                'signed_quantity' => '5.000',
                'movement_type' => 'purchase',
                'reason' => 'Movement type injection',
            ]
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $movement = StockMovement::query()->latest('id')->firstOrFail();

        $this->assertSame(
            StockMovementType::ADJUSTMENT,
            $movement->movement_type
        );
    }
}
