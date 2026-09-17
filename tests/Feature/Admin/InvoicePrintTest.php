<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_print_an_invoice(): void
    {
        $invoice = Invoice::factory()->create();

        $this->get(route('admin.sales.print', $invoice))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_print_an_invoice_receipt(): void
    {
        $admin = User::factory()->admin()->create();

        $product = Product::factory()->create([
            'name' => 'Ruled Notebook',
            'sku' => 'RC-2001',
        ]);

        $customer = Customer::factory()->create([
            'name' => 'Lahiru Fernando',
            'phone' => '0771234567',
        ]);

        $invoice = Invoice::factory()->create([
            'customer_id' => $customer->id,
            'subtotal' => 2000.00,
            'discount_amount' => 100.00,
            'tax_rate' => 10.00,
            'tax_amount' => 190.00,
            'total_amount' => 2090.00,
        ]);

        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 2.000,
            'unit_price' => 1000.00,
            'line_total' => 2000.00,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sales.print', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number)
            ->assertSee('StockPilot Business')
            ->assertSee('Lahiru Fernando')
            ->assertSee('Ruled Notebook')
            ->assertSee('2,000.00')
            ->assertSee('2,090.00')
            ->assertSee('Balance due');
    }

    public function test_sales_user_can_print_an_invoice(): void
    {
        $sales = User::factory()->sales()->create();
        $invoice = Invoice::factory()->create();

        $this->actingAs($sales)
            ->get(route('admin.sales.print', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_stock_user_cannot_print_an_invoice(): void
    {
        $stock = User::factory()->stock()->create();
        $invoice = Invoice::factory()->create();

        $this->actingAs($stock)
            ->get(route('admin.sales.print', $invoice))
            ->assertForbidden();
    }

    public function test_inactive_sales_user_is_logged_out_when_printing(): void
    {
        $sales = User::factory()
            ->sales()
            ->inactive()
            ->create();

        $invoice = Invoice::factory()->create();

        $this->actingAs($sales)
            ->get(route('admin.sales.print', $invoice))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_voided_invoice_receipt_marks_void(): void
    {
        $admin = User::factory()->admin()->create();
        $invoice = Invoice::factory()->voided()->create();

        $this->actingAs($admin)
            ->get(route('admin.sales.print', $invoice))
            ->assertOk()
            ->assertSee('Void Invoice')
            ->assertSee('has been voided.');
    }
}
