<?php

namespace Tests\Feature\Products;

use App\Livewire\Admin\Products\Index;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_products_page(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_products_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Products')
            ->assertSee('Product catalog');
    }

    public function test_stock_user_can_access_products_page(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Products');
    }

    public function test_sales_user_cannot_access_products_page(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_inactive_stock_user_is_logged_out_of_products_page(): void
    {
        $stock = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $this->actingAs($stock)
            ->get(route('admin.products.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_products_can_be_searched(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create([
            'name' => 'Beverages',
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Premium Tea',
            'sku' => 'TEA-001',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'House Coffee',
            'sku' => 'COF-001',
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('search', 'Premium Tea')
            ->assertSee('Premium Tea')
            ->assertDontSee('House Coffee');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $admin = User::factory()->admin()->create();

        $beverages = Category::factory()->create([
            'name' => 'Beverages',
        ]);

        $grocery = Category::factory()->create([
            'name' => 'Grocery',
        ]);

        Product::factory()->create([
            'category_id' => $beverages->id,
            'name' => 'Tea',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'category_id' => $grocery->id,
            'name' => 'Rice',
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('category', (string) $beverages->id)
            ->assertSee('Tea')
            ->assertDontSee('Rice');
    }

    public function test_products_can_be_filtered_by_status(): void
    {
        $admin = User::factory()->admin()->create();

        Product::factory()->create([
            'name' => 'Active Product',
            'status' => 'active',
        ]);

        Product::factory()
            ->inactive()
            ->create([
                'name' => 'Inactive Product',
            ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->assertSee('Active Product')
            ->assertDontSee('Inactive Product')
            ->set('status', 'inactive')
            ->assertSee('Inactive Product')
            ->assertDontSee('Active Product');
    }

    public function test_products_can_be_filtered_by_stock_level(): void
    {
        $admin = User::factory()->admin()->create();

        Product::factory()->create([
            'name' => 'Healthy Stock',
            'quantity' => 50,
            'reorder_level' => 5,
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'Low Stock Item',
            'quantity' => 3,
            'reorder_level' => 10,
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'Out Of Stock Item',
            'quantity' => 0,
            'reorder_level' => 5,
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('status', '')
            ->assertSee('Healthy Stock')
            ->assertSee('Low Stock Item')
            ->assertSee('Out Of Stock Item')
            ->set('stockLevel', 'low')
            ->assertSee('Low Stock Item')
            ->assertDontSee('Healthy Stock')
            ->assertDontSee('Out Of Stock Item')
            ->set('stockLevel', 'out')
            ->assertSee('Out Of Stock Item')
            ->assertDontSee('Healthy Stock')
            ->assertDontSee('Low Stock Item')
            ->call('clearFilters')
            ->assertSee('Healthy Stock');
    }

    public function test_clear_filters_resets_product_results(): void
    {
        $admin = User::factory()->admin()->create();

        Product::factory()->create([
            'name' => 'Premium Tea',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'House Coffee',
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('search', 'Premium Tea')
            ->assertSee('Premium Tea')
            ->assertDontSee('House Coffee')
            ->call('clearFilters')
            ->assertSee('Premium Tea')
            ->assertSee('House Coffee');
    }
}
