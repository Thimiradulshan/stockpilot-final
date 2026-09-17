<?php

namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthorizationHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_customer_page(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::ADMIN,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)
            ->get(route('admin.customers.index'));

        $response
            ->assertOk()
            ->assertSee('Customers')
            ->assertSee('Manage customer records');
    }

    public function test_sales_can_access_customer_page(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)
            ->get(route('admin.customers.index'));

        $response
            ->assertOk()
            ->assertSee('Customers')
            ->assertSee('Manage customer records');
    }

    public function test_stock_cannot_access_customer_page(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::STOCK,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)
            ->get(route('admin.customers.index'));

        $response->assertForbidden();
    }

    public function test_inactive_sales_user_is_logged_out_of_customer_page(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'inactive',
            ])
            ->create();

        $response = $this->actingAs($user)
            ->get(route('admin.customers.index'));

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_cannot_access_customer_page(): void
    {
        $response = $this->get(route('admin.customers.index'));

        $response
            ->assertRedirect();
    }

    public function test_stock_cannot_create_customer_even_with_direct_request(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::STOCK,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'name' => 'Direct Request Customer',
                'status' => 'active',
            ]
        );

        $response->assertForbidden();
    }

    public function test_stock_cannot_update_customer_even_with_direct_request(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::STOCK,
                'status' => 'active',
            ])
            ->create();

        $customer = Customer::factory()->create([
            'name' => 'Protected Customer',
        ]);

        $response = $this->actingAs($user)->patch(
            route('admin.customers.update', $customer),
            [
                'name' => 'Unauthorized Update',
                'status' => 'active',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Protected Customer',
        ]);
    }

    public function test_sales_can_update_customer_even_when_customer_is_inactive(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $customer = Customer::factory()->create([
            'name' => 'Inactive Customer',
            'status' => 'inactive',
        ]);

        $response = $this->actingAs($user)->patch(
            route('admin.customers.update', $customer),
            [
                'name' => 'Reactivated Customer',
                'status' => 'active',
            ]
        );

        $response
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer updated successfully.');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Reactivated Customer',
            'status' => 'active',
        ]);
    }
}
