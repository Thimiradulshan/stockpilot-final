<?php

namespace Tests\Unit\Policies;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SupplierPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_is_available_to_admin_and_stock_users_only(): void
    {
        $policy = new CategoryPolicy;
        $category = Category::factory()->create();

        $this->assertTrue(
            $policy->view(
                User::factory()->admin()->create(),
                $category,
            )
        );

        $this->assertTrue(
            $policy->view(
                User::factory()->stock()->create(),
                $category,
            )
        );

        $this->assertFalse(
            $policy->view(
                User::factory()->sales()->create(),
                $category,
            )
        );
    }

    public function test_supplier_is_available_to_admin_and_stock_users_only(): void
    {
        $policy = new SupplierPolicy;
        $supplier = Supplier::factory()->create();

        $this->assertTrue(
            $policy->view(
                User::factory()->admin()->create(),
                $supplier,
            )
        );

        $this->assertTrue(
            $policy->view(
                User::factory()->stock()->create(),
                $supplier,
            )
        );

        $this->assertFalse(
            $policy->view(
                User::factory()->sales()->create(),
                $supplier,
            )
        );
    }

    public function test_product_is_available_to_admin_and_stock_users_only(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $this->assertTrue(
            $policy->view(
                User::factory()->admin()->create(),
                $product,
            )
        );

        $this->assertTrue(
            $policy->view(
                User::factory()->stock()->create(),
                $product,
            )
        );

        $this->assertFalse(
            $policy->view(
                User::factory()->sales()->create(),
                $product,
            )
        );
    }

    public function test_customer_is_available_to_admin_and_sales_users_only(): void
    {
        $policy = new CustomerPolicy;
        $customer = Customer::factory()->create();

        $this->assertTrue(
            $policy->view(
                User::factory()->admin()->create(),
                $customer,
            )
        );

        $this->assertTrue(
            $policy->view(
                User::factory()->sales()->create(),
                $customer,
            )
        );

        $this->assertFalse(
            $policy->view(
                User::factory()->stock()->create(),
                $customer,
            )
        );
    }

    public function test_inactive_users_cannot_access_master_data(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();

        $inactiveAdmin = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $this->assertFalse(
            (new CategoryPolicy)->view($inactiveAdmin, $category)
        );

        $this->assertFalse(
            (new SupplierPolicy)->view($inactiveAdmin, $supplier)
        );

        $this->assertFalse(
            (new ProductPolicy)->view($inactiveAdmin, $product)
        );

        $this->assertFalse(
            (new CustomerPolicy)->view($inactiveAdmin, $customer)
        );
    }

    public function test_product_stock_adjustment_is_separate_from_product_access(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $this->assertTrue(
            $policy->adjustStock(
                User::factory()->admin()->create(),
                $product,
            )
        );

        $this->assertTrue(
            $policy->adjustStock(
                User::factory()->stock()->create(),
                $product,
            )
        );

        $this->assertFalse(
            $policy->adjustStock(
                User::factory()->sales()->create(),
                $product,
            )
        );
    }

    public function test_master_data_deletion_is_disabled(): void
    {
        $category = Category::factory()->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $customer = Customer::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->assertFalse(
            (new CategoryPolicy)->delete($admin, $category)
        );

        $this->assertFalse(
            (new SupplierPolicy)->delete($admin, $supplier)
        );

        $this->assertFalse(
            (new ProductPolicy)->delete($admin, $product)
        );

        $this->assertFalse(
            (new CustomerPolicy)->delete($admin, $customer)
        );
    }
}
