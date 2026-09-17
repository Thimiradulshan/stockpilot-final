<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Statements\Index as StatementsIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StatementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.statements.index'))
            ->assertRedirect(route('login'));
    }

    public function test_stock_user_cannot_access_statements(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('admin.statements.index'))
            ->assertForbidden();

        Livewire::actingAs($stock)
            ->test(StatementsIndex::class)
            ->assertForbidden();
    }

    public function test_sales_user_can_access_statements_page(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->get(route('admin.statements.index'))
            ->assertOk()
            ->assertSee('Customer statement');
    }

    public function test_statement_requires_a_selected_customer(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(StatementsIndex::class)
            ->assertOk()
            ->assertSee('Choose a customer');
    }

    public function test_statement_lists_invoices_payments_and_running_balance(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->create(['name' => 'Ruwan Silva']);

        $first = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'invoice_date' => today()->subDays(3),
            'total_amount' => 5000.00,
            'status' => 'completed',
            'payment_status' => 'partially_paid',
        ]);

        Payment::factory()->create([
            'invoice_id' => $first->id,
            'payment_date' => today()->subDays(2),
            'amount' => 2000.00,
        ]);

        $second = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'invoice_date' => today()->subDay(),
            'total_amount' => 3000.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        Livewire::actingAs($sales)
            ->test(StatementsIndex::class)
            ->set('customer', (string) $customer->id)
            ->assertOk()
            ->assertSee($first->invoice_number)
            ->assertSee($second->invoice_number)
            ->assertSee('Rs. 8,000.00')
            ->assertSee('Rs. 6,000.00')
            ->assertSee('Rs. 3,000.00');
    }

    public function test_statement_ignores_invoices_outside_the_date_range(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->create(['name' => 'Tharindu Rathnayake']);

        Invoice::factory()->create([
            'customer_id' => $customer->id,
            'invoice_date' => today()->subMonths(2),
            'total_amount' => 9500.00,
            'status' => 'completed',
            'payment_status' => 'unpaid',
        ]);

        Livewire::actingAs($sales)
            ->test(StatementsIndex::class)
            ->set('customer', (string) $customer->id)
            ->set('from', today()->startOfMonth()->toDateString())
            ->set('to', today()->toDateString())
            ->assertOk()
            ->assertSee('No activity in this period')
            ->assertDontSee('Rs. 9,500.00');
    }
}
