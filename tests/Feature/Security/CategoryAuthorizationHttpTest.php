<?php

namespace Tests\Feature\Security;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryAuthorizationHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_create_category(): void
    {
        $this->post(route('admin.categories.store'), [
            'name' => 'Guest Category',
            'description' => null,
            'status' => 'active',
        ])->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $this->patch(
            route('admin.categories.update', $category),
            [
                'name' => 'Guest Update',
                'description' => null,
                'status' => 'active',
            ]
        )->assertRedirect(route('login'));
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Admin Category',
                'description' => null,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Admin Updated Category',
                    'description' => null,
                    'status' => 'active',
                ]
            )
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_stock_user_can_create_category(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->post(route('admin.categories.store'), [
                'name' => 'Stock Category',
                'description' => null,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_stock_user_can_update_category(): void
    {
        $stock = User::factory()->stock()->create();
        $category = Category::factory()->create();

        $this->actingAs($stock)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Stock Updated Category',
                    'description' => null,
                    'status' => 'active',
                ]
            )
            ->assertRedirect(route('admin.categories.index'));
    }

    public function test_sales_user_cannot_create_category(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->post(route('admin.categories.store'), [
                'name' => 'Sales Forbidden',
                'description' => null,
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_sales_user_cannot_update_category(): void
    {
        $sales = User::factory()->sales()->create();
        $category = Category::factory()->create();

        $this->actingAs($sales)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Sales Forbidden Update',
                    'description' => null,
                    'status' => 'inactive',
                ]
            )
            ->assertForbidden();
    }

    public function test_inactive_admin_is_logged_out_when_creating_category(): void
    {
        $admin = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Inactive Admin Category',
                'description' => null,
                'status' => 'active',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_stock_user_is_logged_out_when_updating_category(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $category = Category::factory()->create();

        $this->actingAs($stock)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Inactive Stock Update',
                    'description' => null,
                    'status' => 'inactive',
                ]
            )
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
