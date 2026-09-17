<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function seedBusinessData(): void
    {
        Supplier::factory()->create(['name' => 'Paper House Lanka', 'phone' => '011-2223334']);

        Product::factory()->create([
            'name' => 'Heavy Stock Item',
            'sku' => 'DASH-A',
            'cost_price' => 100.00,
            'selling_price' => 150.00,
            'quantity' => '10.000',
            'reorder_level' => '5.000',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'Low Stock Item',
            'sku' => 'DASH-B',
            'cost_price' => 50.00,
            'selling_price' => 80.00,
            'quantity' => '3.000',
            'reorder_level' => '5.000',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'Out Of Stock Item',
            'sku' => 'DASH-C',
            'cost_price' => 25.00,
            'selling_price' => 45.00,
            'quantity' => '0.000',
            'reorder_level' => '5.000',
            'status' => 'active',
        ]);

        $customer = Customer::factory()->create();

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'invoice_date' => today(),
            'subtotal' => 5000.00,
            'discount_amount' => 0.00,
            'tax_rate' => 0.00,
            'tax_amount' => 0.00,
            'total_amount' => 5000.00,
            'status' => 'completed',
            'payment_status' => 'partially_paid',
        ]);

        Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'payment_date' => now(),
            'amount' => 2000.00,
        ]);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_sees_sales_and_stock_kpis_with_correct_values(): void
    {
        $this->seedBusinessData();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rs. 5,000.00')
            ->assertSee('Rs. 3,000.00')
            ->assertSee('Rs. 1,150.00')
            ->assertSee('Low stock')
            ->assertSee('Out of stock')
            ->assertSee('Low Stock Item')
            ->assertSee('Total sales')
            ->assertSee('Total products')
            ->assertSee('Total suppliers');
    }

    public function test_stock_value_and_counts_are_calculated_correctly(): void
    {
        $this->seedBusinessData();

        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rs. 1,150.00')
            ->assertSee('Total products')
            ->assertSee('Total suppliers')
            ->assertDontSee('Sales today');
    }

    public function test_sales_user_sees_sales_kpis_but_not_stock_value(): void
    {
        $this->seedBusinessData();

        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rs. 5,000.00')
            ->assertSee('Rs. 3,000.00')
            ->assertSee('Total sales')
            ->assertDontSee('Rs. 1,150.00')
            ->assertDontSee('Low stock')
            ->assertDontSee('Total products')
            ->assertDontSee('Total suppliers');
    }

    public function test_admin_sees_dashboard_hierarchy_and_all_quick_actions(): void
    {
        $this->seedBusinessData();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Business status')
            ->assertSee('Sales overview')
            ->assertSee('Quick actions')
            ->assertSee('New sale')
            ->assertSee('Record payment')
            ->assertSee('Add product')
            ->assertSee('Add supplier')
            ->assertSee('Add customer')
            ->assertSee(route('admin.pos.index'));
    }

    public function test_sales_user_only_sees_sales_quick_actions(): void
    {
        $this->seedBusinessData();

        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('New sale')
            ->assertSee('Record payment')
            ->assertSee('Add customer')
            ->assertDontSee('Add product')
            ->assertDontSee('Add supplier')
            ->assertDontSee('Low stock')
            ->assertDontSee('Total products')
            ->assertDontSee('Total suppliers')
            ->assertDontSee('Rs. 1,150.00')
            ->assertDontSee('Sales today');
    }

    public function test_stock_user_only_sees_stock_quick_actions(): void
    {
        $this->seedBusinessData();

        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Add product')
            ->assertSee('Add supplier')
            ->assertSee('Low stock')
            ->assertDontSee('New sale')
            ->assertDontSee('Record payment')
            ->assertDontSee('Add customer')
            ->assertDontSee('Rs. 5,000.00')
            ->assertDontSee('Sales today');
    }

    public function test_sales_overview_shows_empty_state_without_sales(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('No sales history yet')
            ->assertSee('Quick actions');
    }

    public function test_today_hero_reports_customers_served(): void
    {
        $this->seedBusinessData();

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('customers served')
            ->assertSee('invoices completed');
    }
}
