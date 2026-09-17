<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(
            route('admin.categories.store'),
            [
                'name' => 'Electronics',
                'description' => 'Electronic products',
                'status' => 'active',
            ]
        );

        $response
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success', 'Category created successfully.');

        $this->assertDatabaseHas('categories', [
            'name' => 'Electronics',
            'description' => 'Electronic products',
            'status' => 'active',
        ]);
    }

    public function test_stock_user_can_create_a_category(): void
    {
        $stock = User::factory()->stock()->create();

        $response = $this->actingAs($stock)->post(
            route('admin.categories.store'),
            [
                'name' => 'Stationery',
                'description' => null,
                'status' => 'active',
            ]
        );

        $response->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'name' => 'Stationery',
            'status' => 'active',
        ]);
    }

    public function test_sales_user_cannot_create_a_category(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->post(route('admin.categories.store'), [
                'name' => 'Forbidden',
                'description' => null,
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('categories', [
            'name' => 'Forbidden',
        ]);
    }

    public function test_inactive_stock_user_is_logged_out_when_creating_a_category(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $this->actingAs($stock)
            ->post(route('admin.categories.store'), [
                'name' => 'Inactive User Category',
                'description' => null,
                'status' => 'active',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertDatabaseMissing('categories', [
            'name' => 'Inactive User Category',
        ]);
    }

    public function test_category_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'description' => 'No name',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_category_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();

        Category::factory()->create([
            'name' => 'Electronics',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Electronics',
                'description' => null,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_category_status_must_be_valid(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'Invalid Status',
                'description' => null,
                'status' => 'something-else',
            ])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_can_update_a_category(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create([
            'name' => 'Old Name',
            'description' => 'Old description',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->patch(
            route('admin.categories.update', $category),
            [
                'name' => 'New Name',
                'description' => 'New description',
                'status' => 'inactive',
            ]
        );

        $response
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('success', 'Category updated successfully.');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'New Name',
            'description' => 'New description',
            'status' => 'inactive',
        ]);
    }

    public function test_stock_user_can_update_a_category(): void
    {
        $stock = User::factory()->stock()->create();

        $category = Category::factory()->create();

        $this->actingAs($stock)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Stock Updated',
                    'description' => 'Updated by stock user',
                    'status' => 'active',
                ]
            )
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Stock Updated',
        ]);
    }

    public function test_sales_user_cannot_update_a_category(): void
    {
        $sales = User::factory()->sales()->create();

        $category = Category::factory()->create([
            'name' => 'Protected Category',
        ]);

        $this->actingAs($sales)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Should Not Update',
                    'description' => null,
                    'status' => 'inactive',
                ]
            )
            ->assertForbidden();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Protected Category',
            'status' => 'active',
        ]);
    }

    public function test_inactive_stock_user_is_logged_out_when_updating_a_category(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $category = Category::factory()->create([
            'name' => 'Protected Category',
        ]);

        $this->actingAs($stock)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Should Not Update',
                    'description' => null,
                    'status' => 'inactive',
                ]
            )
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_update_allows_category_to_keep_its_existing_name(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create([
            'name' => 'Electronics',
        ]);

        $this->actingAs($admin)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Electronics',
                    'description' => 'Updated',
                    'status' => 'active',
                ]
            )
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Electronics',
            'description' => 'Updated',
        ]);
    }

    public function test_category_update_does_not_change_existing_product_assignment(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create([
            'name' => 'Electronics',
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->actingAs($admin)
            ->patch(
                route('admin.categories.update', $category),
                [
                    'name' => 'Electronics',
                    'description' => 'Deactivated',
                    'status' => 'inactive',
                ]
            )
            ->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $category->id,
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'status' => 'inactive',
        ]);
    }

    public function test_category_deletion_is_not_available(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.categories.index'))
            ->assertMethodNotAllowed();

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
