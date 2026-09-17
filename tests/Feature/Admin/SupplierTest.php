<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_supplier(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.suppliers.store'),
            [
                'name' => 'ABC Supplies',
                'company' => 'ABC Supplies (Pvt) Ltd',
                'phone' => '0712345678',
                'email' => 'contact@example.com',
                'address' => 'Anuradhapura',
                'status' => 'active',
            ],
        );

        $response
            ->assertRedirect(route('admin.suppliers.index'))
            ->assertSessionHas('success', 'Supplier created successfully.');

        $this->assertDatabaseHas('suppliers', [
            'name' => 'ABC Supplies',
            'company' => 'ABC Supplies (Pvt) Ltd',
            'phone' => '0712345678',
            'email' => 'contact@example.com',
            'status' => 'active',
        ]);
    }

    public function test_stock_user_can_create_a_supplier(): void
    {
        $stock = User::factory()->stock()->create();

        $response = $this->actingAs($stock)->post(
            route('admin.suppliers.store'),
            [
                'name' => 'Stock Supplier',
                'status' => 'active',
            ],
        );

        $response->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'name' => 'Stock Supplier',
            'status' => 'active',
        ]);
    }

    public function test_sales_user_cannot_create_a_supplier(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Unauthorized Supplier',
                    'status' => 'active',
                ],
            )
            ->assertForbidden();

        $this->assertDatabaseMissing('suppliers', [
            'name' => 'Unauthorized Supplier',
        ]);
    }

    public function test_inactive_stock_user_is_logged_out_when_creating_a_supplier(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $this->actingAs($stock)
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Blocked Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertDatabaseMissing('suppliers', [
            'name' => 'Blocked Supplier',
        ]);
    }

    public function test_supplier_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.suppliers.index'))
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => '',
                    'status' => 'active',
                ],
            )
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('suppliers', 0);
    }

    public function test_supplier_email_must_be_valid(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.suppliers.index'))
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Invalid Email Supplier',
                    'email' => 'not-an-email',
                    'status' => 'active',
                ],
            )
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('suppliers', [
            'name' => 'Invalid Email Supplier',
        ]);
    }

    public function test_supplier_status_must_be_valid(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.suppliers.index'))
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Invalid Status Supplier',
                    'status' => 'deleted',
                ],
            )
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('suppliers', [
            'name' => 'Invalid Status Supplier',
        ]);
    }

    public function test_admin_can_update_a_supplier(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create([
            'name' => 'Old Supplier',
            'company' => 'Old Company',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->patch(
            route('admin.suppliers.update', $supplier),
            [
                'name' => 'Updated Supplier',
                'company' => 'Updated Company',
                'phone' => '0700000000',
                'email' => 'updated@example.com',
                'address' => 'Updated address',
                'status' => 'inactive',
            ],
        );

        $response
            ->assertRedirect(route('admin.suppliers.index'))
            ->assertSessionHas('success', 'Supplier updated successfully.');

        $supplier->refresh();

        $this->assertSame('Updated Supplier', $supplier->name);
        $this->assertSame('Updated Company', $supplier->company);
        $this->assertSame('0700000000', $supplier->phone);
        $this->assertSame('updated@example.com', $supplier->email);
        $this->assertSame('Updated address', $supplier->address);
        $this->assertSame('inactive', $supplier->status);
    }

    public function test_stock_user_can_update_a_supplier(): void
    {
        $stock = User::factory()->stock()->create();

        $supplier = Supplier::factory()->create([
            'name' => 'Original Supplier',
        ]);

        $this->actingAs($stock)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Stock Updated Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Stock Updated Supplier',
        ]);
    }

    public function test_sales_user_cannot_update_a_supplier(): void
    {
        $sales = User::factory()->sales()->create();

        $supplier = Supplier::factory()->create([
            'name' => 'Protected Supplier',
        ]);

        $this->actingAs($sales)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Unauthorized Update',
                    'status' => 'active',
                ],
            )
            ->assertForbidden();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Protected Supplier',
        ]);
    }

    public function test_inactive_stock_user_is_logged_out_when_updating_a_supplier(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $supplier = Supplier::factory()->create([
            'name' => 'Protected Supplier',
        ]);

        $this->actingAs($stock)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Unauthorized Update',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Protected Supplier',
        ]);
    }

    public function test_supplier_update_does_not_change_existing_product_relationships(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $product = Product::factory()->create();

        $supplier->products()->attach($product->id, [
            'supplier_product_code' => 'SUP-001',
            'last_cost' => 125.50,
            'is_preferred' => true,
        ]);

        $this->actingAs($admin)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Updated Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('admin.suppliers.index'));

        $this->assertDatabaseHas('product_supplier', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'supplier_product_code' => 'SUP-001',
            'last_cost' => '125.50',
            'is_preferred' => 1,
        ]);
    }

    public function test_supplier_delete_endpoint_is_not_available(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $this->actingAs($admin)
            ->delete(
                route('admin.suppliers.index'),
            )
            ->assertMethodNotAllowed();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
        ]);
    }
}
