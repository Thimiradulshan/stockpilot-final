<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_customer(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::ADMIN,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'name' => 'ABC Customer',
                'phone' => '0771234567',
                'email' => 'abc@example.com',
                'address' => 'Anuradhapura',
                'status' => 'active',
            ]
        );

        $response
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer created successfully.');

        $this->assertDatabaseHas('customers', [
            'name' => 'ABC Customer',
            'phone' => '0771234567',
            'email' => 'abc@example.com',
            'address' => 'Anuradhapura',
            'status' => 'active',
        ]);
    }

    public function test_sales_user_can_create_customer(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'name' => 'Sales Customer',
                'phone' => '0770000000',
                'email' => 'sales@example.com',
                'address' => 'Kandy',
                'status' => 'active',
            ]
        );

        $response
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer created successfully.');

        $this->assertDatabaseHas('customers', [
            'name' => 'Sales Customer',
        ]);
    }

    public function test_stock_user_cannot_create_customer(): void
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
                'name' => 'Blocked Customer',
                'status' => 'active',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseMissing('customers', [
            'name' => 'Blocked Customer',
        ]);
    }

    public function test_inactive_user_is_logged_out_when_creating_customer(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'inactive',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'name' => 'Inactive User Customer',
                'status' => 'active',
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_customer_name_is_required(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'phone' => '0771234567',
                'status' => 'active',
            ]
        );

        $response
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('customers', 0);
    }

    public function test_customer_email_must_be_valid(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'name' => 'Invalid Email Customer',
                'email' => 'not-an-email',
                'status' => 'active',
            ]
        );

        $response
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('customers', [
            'name' => 'Invalid Email Customer',
        ]);
    }

    public function test_customer_status_must_be_valid(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $response = $this->actingAs($user)->post(
            route('admin.customers.store'),
            [
                'name' => 'Invalid Status Customer',
                'status' => 'deleted',
            ]
        );

        $response
            ->assertSessionHasErrors('status');

        $this->assertDatabaseMissing('customers', [
            'name' => 'Invalid Status Customer',
        ]);
    }

    public function test_admin_can_update_customer(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::ADMIN,
                'status' => 'active',
            ])
            ->create();

        $customer = Customer::factory()->create([
            'name' => 'Original Customer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->patch(
            route('admin.customers.update', $customer),
            [
                'name' => 'Updated Customer',
                'phone' => '0711111111',
                'email' => 'updated@example.com',
                'address' => 'Colombo',
                'status' => 'inactive',
            ]
        );

        $response
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer updated successfully.');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Updated Customer',
            'phone' => '0711111111',
            'email' => 'updated@example.com',
            'address' => 'Colombo',
            'status' => 'inactive',
        ]);
    }

    public function test_sales_user_can_update_customer(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'active',
            ])
            ->create();

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->patch(
            route('admin.customers.update', $customer),
            [
                'name' => 'Sales Updated Customer',
                'phone' => null,
                'email' => null,
                'address' => null,
                'status' => 'active',
            ]
        );

        $response
            ->assertRedirect(route('admin.customers.index'))
            ->assertSessionHas('success', 'Customer updated successfully.');

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Sales Updated Customer',
        ]);
    }

    public function test_stock_user_cannot_update_customer(): void
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
                'name' => 'Should Not Update',
                'status' => 'active',
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Protected Customer',
        ]);
    }

    public function test_inactive_user_is_logged_out_when_updating_customer(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::SALES,
                'status' => 'inactive',
            ])
            ->create();

        $customer = Customer::factory()->create([
            'name' => 'Protected Customer',
        ]);

        $response = $this->actingAs($user)->patch(
            route('admin.customers.update', $customer),
            [
                'name' => 'Should Not Update',
                'status' => 'active',
            ]
        );

        $response->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => 'Protected Customer',
        ]);
    }

    public function test_customer_update_preserves_invoice_relationship(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::ADMIN,
                'status' => 'active',
            ])
            ->create();

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $response = $this->actingAs($user)->patch(
            route('admin.customers.update', $customer),
            [
                'name' => 'Updated With Invoice',
                'status' => 'active',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'customer_id' => $customer->id,
        ]);
    }

    public function test_customer_delete_endpoint_does_not_exist(): void
    {
        $user = User::factory()
            ->state([
                'role' => UserRole::ADMIN,
                'status' => 'active',
            ])
            ->create();

        $customer = Customer::factory()->create();

        $response = $this->actingAs($user)->delete(
            route('admin.customers.index').'/'.$customer->id
        );

        $response->assertMethodNotAllowed();

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
        ]);
    }
}
