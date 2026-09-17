<?php

namespace Tests\Feature\Security;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierAuthorizationHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_supplier(): void
    {
        $this->post(
            route('admin.suppliers.store'),
            [
                'name' => 'Guest Supplier',
                'status' => 'active',
            ],
        )->assertRedirect();

        $this->assertDatabaseMissing('suppliers', [
            'name' => 'Guest Supplier',
        ]);
    }

    public function test_guest_cannot_update_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->patch(
            route('admin.suppliers.update', $supplier),
            [
                'name' => 'Unauthorized Supplier',
                'status' => 'active',
            ],
        )->assertRedirect();

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => $supplier->name,
        ]);
    }

    public function test_admin_can_create_supplier(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Admin Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('admin.suppliers.index'));
    }

    public function test_admin_can_update_supplier(): void
    {
        $admin = User::factory()->admin()->create();

        $supplier = Supplier::factory()->create();

        $this->actingAs($admin)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Admin Updated Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('admin.suppliers.index'));
    }

    public function test_stock_user_can_create_supplier(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Stock Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('admin.suppliers.index'));
    }

    public function test_stock_user_can_update_supplier(): void
    {
        $stock = User::factory()->stock()->create();

        $supplier = Supplier::factory()->create();

        $this->actingAs($stock)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Stock Updated Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('admin.suppliers.index'));
    }

    public function test_sales_user_cannot_create_supplier(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Sales Supplier',
                    'status' => 'active',
                ],
            )
            ->assertForbidden();
    }

    public function test_sales_user_cannot_update_supplier(): void
    {
        $sales = User::factory()->sales()->create();

        $supplier = Supplier::factory()->create();

        $this->actingAs($sales)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Sales Updated Supplier',
                    'status' => 'active',
                ],
            )
            ->assertForbidden();
    }

    public function test_inactive_admin_is_logged_out_when_creating_supplier(): void
    {
        $admin = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $this->actingAs($admin)
            ->post(
                route('admin.suppliers.store'),
                [
                    'name' => 'Inactive Admin Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_stock_user_is_logged_out_when_updating_supplier(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $supplier = Supplier::factory()->create();

        $this->actingAs($stock)
            ->patch(
                route('admin.suppliers.update', $supplier),
                [
                    'name' => 'Inactive Stock Supplier',
                    'status' => 'active',
                ],
            )
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
