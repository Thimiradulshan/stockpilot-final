<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesKpiTest extends TestCase
{
    use RefreshDatabase;

    public function test_outstanding_balance_kpi_subtracts_recorded_payments(): void
    {
        $admin = User::factory()->admin()->create();

        $partialInvoice = Invoice::factory()
            ->create([
                'total_amount' => '1000.00',
                'payment_status' => 'partially_paid',
            ]);

        Payment::factory()->create([
            'invoice_id' => $partialInvoice->id,
            'amount' => '800.00',
        ]);

        Invoice::factory()
            ->paid()
            ->create([
                'total_amount' => '500.00',
            ]);

        Invoice::factory()
            ->create([
                'total_amount' => '300.00',
                'payment_status' => 'unpaid',
            ]);

        $this->actingAs($admin)
            ->get(route('admin.sales.index'))
            ->assertOk()
            ->assertSee('Rs 500.00');
    }

    public function test_outstanding_balance_kpi_is_zero_when_every_completed_invoice_is_paid(): void
    {
        $admin = User::factory()->admin()->create();

        $paidInvoice = Invoice::factory()
            ->paid()
            ->create([
                'total_amount' => '1000.00',
            ]);

        Payment::factory()->create([
            'invoice_id' => $paidInvoice->id,
            'amount' => '1000.00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sales.index'))
            ->assertOk()
            ->assertSee('Rs 0.00');
    }
}
