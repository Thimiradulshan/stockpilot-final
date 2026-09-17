<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Reports\Index as ReportsIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.reports.index'))
            ->assertRedirect(route('login'));
    }

    public function test_inactive_user_is_redirected_to_login(): void
    {
        $user = User::factory()->admin()->inactive()->create();

        $this->get(route('admin.reports.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_defaults_to_sales_section(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(ReportsIndex::class)
            ->assertOk()
            ->assertSet('section', 'sales');
    }

    public function test_admin_can_open_every_section(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (['sales', 'purchases', 'profit', 'valuation', 'vat', 'outstanding'] as $section) {
            Livewire::actingAs($admin)
                ->test(ReportsIndex::class)
                ->set('section', $section)
                ->assertOk()
                ->assertSet('section', $section);
        }
    }

    public function test_sales_user_is_clamped_to_sales_and_cannot_run_other_sections(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(ReportsIndex::class)
            ->assertOk()
            ->assertSet('section', 'sales');
    }

    public function test_sales_user_can_open_sales_and_vat_but_not_purchases_profit_or_valuation(): void
    {
        $sales = User::factory()->sales()->create();

        foreach (['sales', 'vat', 'outstanding'] as $allowed) {
            Livewire::actingAs($sales)
                ->test(ReportsIndex::class)
                ->set('section', $allowed)
                ->assertOk();
        }

        foreach (['purchases', 'profit', 'valuation'] as $forbidden) {
            Livewire::actingAs($sales)
                ->test(ReportsIndex::class)
                ->set('section', $forbidden)
                ->assertForbidden();
        }
    }

    public function test_stock_user_can_open_purchases_and_valuation_but_not_sales_profit_or_vat(): void
    {
        $stock = User::factory()->stock()->create();

        foreach (['purchases', 'valuation'] as $allowed) {
            Livewire::actingAs($stock)
                ->test(ReportsIndex::class)
                ->set('section', $allowed)
                ->assertOk();
        }

        foreach (['sales', 'profit', 'vat', 'outstanding'] as $forbidden) {
            Livewire::actingAs($stock)
                ->test(ReportsIndex::class)
                ->set('section', $forbidden)
                ->assertForbidden();
        }
    }

    public function test_csv_export_streams_a_download_for_the_active_section(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(ReportsIndex::class)
            ->set('section', 'sales')
            ->set('from', '2026-01-01')
            ->set('to', '2026-01-31')
            ->call('exportCsv')
            ->assertFileDownloaded('sales-report-2026-01-01-to-2026-01-31.csv');
    }

    public function test_customer_filter_accepts_a_valid_customer_id(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create(['name' => 'Lahiru Fernando']);

        Livewire::actingAs($admin)
            ->test(ReportsIndex::class)
            ->set('section', 'sales')
            ->set('customer', (string) $customer->id)
            ->assertOk()
            ->assertSet('customer', (string) $customer->id);
    }

    public function test_customer_filter_ignores_invalid_ids_from_url(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['section' => 'sales', 'cust' => '999999']))
            ->assertOk()
            ->assertSee('All customers');
    }

    public function test_outstanding_report_lists_only_customers_with_a_balance(): void
    {
        $admin = User::factory()->admin()->create();

        $withBalance = Customer::factory()->create(['name' => 'Delta Buyer']);
        $settled = Customer::factory()->create(['name' => 'Settled Buyer']);

        $openInvoice = Invoice::factory()->create([
            'customer_id' => $withBalance->id,
            'invoice_date' => now()->subDays(2),
            'total_amount' => 1000.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        $settledInvoice = Invoice::factory()->create([
            'customer_id' => $settled->id,
            'invoice_date' => now()->subDay(),
            'total_amount' => 500.00,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        Payment::factory()->create([
            'invoice_id' => $settledInvoice->id,
            'payment_date' => now(),
            'amount' => 500.00,
        ]);

        Payment::factory()->create([
            'invoice_id' => $openInvoice->id,
            'payment_date' => now(),
            'amount' => 400.00,
        ]);

        Livewire::actingAs($admin)
            ->test(ReportsIndex::class)
            ->set('section', 'outstanding')
            ->set('from', now()->subMonth()->toDateString())
            ->set('to', now()->toDateString())
            ->assertOk()
            ->assertSee('Delta Buyer')
            ->assertSee('600.00')
            ->assertDontSee('Settled Buyer');
    }

    public function test_valuation_report_marks_stock_status(): void
    {
        $admin = User::factory()->admin()->create();

        Product::factory()->create([
            'name' => 'Immediate Stock',
            'sku' => 'VAL-OUT',
            'quantity' => '0.000',
            'reorder_level' => '5.000',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'Replenish Stock',
            'sku' => 'VAL-LOW',
            'quantity' => '3.000',
            'reorder_level' => '5.000',
            'status' => 'active',
        ]);

        Product::factory()->create([
            'name' => 'Healthy Stock',
            'sku' => 'VAL-OK',
            'quantity' => '10.000',
            'reorder_level' => '5.000',
            'status' => 'active',
        ]);

        Livewire::actingAs($admin)
            ->test(ReportsIndex::class)
            ->set('section', 'valuation')
            ->assertOk()
            ->assertSee('Immediate Stock')
            ->assertSee('Replenish Stock')
            ->assertSee('Healthy Stock')
            ->assertSee('Out of Stock')
            ->assertSee('Low Stock')
            ->assertSee('In Stock');
    }
}
