<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_default_admin(): void
    {
        $this->seed();

        $admin = User::query()
            ->where('email', config('stockpilot.default_admin.email'))
            ->firstOrFail();

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isActive());
        $this->assertTrue(Hash::check('password', $admin->password));
    }

    public function test_database_seeder_is_idempotent_for_admin(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(
            1,
            User::query()
                ->where('email', config('stockpilot.default_admin.email'))
                ->count(),
        );
    }

    public function test_admin_seeder_respects_environment_configuration(): void
    {
        config()->set('stockpilot.default_admin.name', 'Owner');
        config()->set('stockpilot.default_admin.email', 'owner@example.test');
        config()->set('stockpilot.default_admin.password', 'strongsecret');

        try {
            $this->seed();

            $admin = User::query()
                ->where('email', 'owner@example.test')
                ->firstOrFail();

            $this->assertSame('Owner', $admin->name);
            $this->assertTrue($admin->isAdmin());
            $this->assertTrue(Hash::check('strongsecret', $admin->password));
        } finally {
            config()->set('stockpilot.default_admin.name', 'System Administrator');
            config()->set('stockpilot.default_admin.email', 'admin@stockpilot.app');
            config()->set('stockpilot.default_admin.password', 'password');
        }
    }

    public function test_demo_seeder_is_skipped_by_default(): void
    {
        config()->set('stockpilot.seed_demo', false);

        try {
            $this->seed();

            $this->assertSame(0, Product::query()->count());
            $this->assertSame(1, User::query()->count());
        } finally {
            config()->set('stockpilot.seed_demo', false);
        }
    }

    public function test_demo_seeder_creates_master_data_when_enabled(): void
    {
        config()->set('stockpilot.seed_demo', true);

        try {
            $this->seed();

            $this->assertSame(3, Category::query()->count());
            $this->assertSame(3, Supplier::query()->count());
            $this->assertSame(7, Customer::query()->count());
            $this->assertSame(8, Product::query()->count());
            $this->assertSame(8, StockMovement::query()->count());
            $this->assertSame(3, User::query()->count());
        } finally {
            config()->set('stockpilot.seed_demo', false);
        }
    }

    public function test_demo_seeder_does_not_duplicate_records(): void
    {
        config()->set('stockpilot.seed_demo', true);

        try {
            $this->seed();
            $this->seed();

            $this->assertSame(3, Category::query()->count());
            $this->assertSame(3, Supplier::query()->count());
            $this->assertSame(7, Customer::query()->count());
            $this->assertSame(8, Product::query()->count());
            $this->assertSame(8, StockMovement::query()->count());
            $this->assertSame(3, User::query()->count());
        } finally {
            config()->set('stockpilot.seed_demo', false);
        }
    }

    public function test_demo_seeder_creates_sales_and_stock_users_when_enabled(): void
    {
        config()->set('stockpilot.seed_demo', true);

        try {
            $this->seed();

            $sales = User::query()->where('email', 'sales@stockpilot.app')->firstOrFail();
            $stock = User::query()->where('email', 'stock@stockpilot.app')->firstOrFail();

            $this->assertTrue($sales->isSalesUser());
            $this->assertTrue($stock->isStockUser());
            $this->assertTrue($sales->isActive());
            $this->assertTrue($stock->isActive());
            $this->assertTrue(Hash::check('password', $sales->password));
            $this->assertTrue(Hash::check('password', $stock->password));
        } finally {
            config()->set('stockpilot.seed_demo', false);
        }
    }
}
