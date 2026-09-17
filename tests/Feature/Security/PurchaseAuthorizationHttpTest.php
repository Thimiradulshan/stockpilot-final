<?php

namespace Tests\Feature\Security;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseAuthorizationHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_purchase_area(): void
    {
        $this->get(route('admin.purchases.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_purchase_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.purchases.index'))
            ->assertOk()
            ->assertSee('Purchases')
            ->assertSee('Record stock purchases and manage incoming inventory.')
            ->assertSee('New purchase');
    }

    public function test_stock_user_can_access_purchase_area(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('admin.purchases.index'))
            ->assertOk()
            ->assertSee('Purchases')
            ->assertSee('Record stock purchases and manage incoming inventory.')
            ->assertSee('New purchase');
    }

    public function test_sales_user_cannot_access_purchase_area(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->get(route('admin.purchases.index'))
            ->assertForbidden();
    }

    public function test_inactive_stock_user_is_logged_out_of_purchase_area(): void
    {
        $stock = User::factory()->stock()->create([
            'status' => 'inactive',
        ]);

        $this->actingAs($stock)
            ->get(route('admin.purchases.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_sales_user_cannot_cancel_a_purchase(): void
    {
        $sales = User::factory()->sales()->create();

        $supplier = Supplier::factory()->create();

        $purchase = Purchase::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => 'completed',
        ]);

        $this->actingAs($sales)
            ->post(route('admin.purchases.cancel', $purchase))
            ->assertForbidden();
    }

    public function test_stock_user_can_reach_purchase_cancellation_authorization_boundary(): void
    {
        $stock = User::factory()->stock()->create();

        $supplier = Supplier::factory()->create();

        $purchase = Purchase::factory()->create([
            'supplier_id' => $supplier->id,
            'status' => 'completed',
        ]);

        $this->mock(PurchaseService::class, function ($mock) use ($purchase) {
            $mock->shouldReceive('cancel')
                ->once()
                ->andReturn($purchase);
        });

        $this->actingAs($stock)
            ->post(route('admin.purchases.cancel', $purchase))
            ->assertRedirect(route('admin.purchases.index'));
    }

    public function test_sales_user_cannot_create_a_purchase(): void
    {
        $sales = User::factory()->sales()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'status' => 'active',
        ]);

        $payload = [
            'purchase_number' => 'PO-AUTH-001',
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

        $this->actingAs($sales)
            ->post(route('admin.purchases.store'), $payload)
            ->assertForbidden();
    }

    public function test_inactive_stock_user_is_logged_out_when_creating_a_purchase(): void
    {
        $stock = User::factory()->stock()->create([
            'status' => 'inactive',
        ]);

        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create([
            'status' => 'active',
        ]);

        $payload = [
            'purchase_number' => 'PO-AUTH-002',
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

        $this->actingAs($stock)
            ->post(route('admin.purchases.store'), $payload)
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
