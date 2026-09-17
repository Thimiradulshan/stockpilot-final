<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentService = new PaymentService;
    }

    public function test_payment_can_be_created_for_an_unpaid_invoice(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
            paymentStatus: 'unpaid',
        );

        $payment = $this->paymentService->create(
            invoice: $invoice,
            amount: '250.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-001',
            actor: $user,
        );

        $this->assertSame(
            $invoice->id,
            $payment->invoice_id
        );

        $this->assertSame(
            '250.00',
            (string) $payment->amount
        );

        $this->assertSame(
            'cash',
            $payment->payment_method
        );

        $this->assertSame(
            'PAY-001',
            $payment->idempotency_key
        );

        $this->assertSame(
            $user->id,
            $payment->received_by
        );

        $this->assertSame(
            'partially_paid',
            $invoice->fresh()->payment_status
        );
    }

    public function test_payment_can_complete_an_invoice(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
            paymentStatus: 'unpaid',
        );

        $payment = $this->paymentService->create(
            invoice: $invoice,
            amount: '1000.00',
            paymentMethod: 'bank_transfer',
            idempotencyKey: 'PAY-002',
            actor: $user,
        );

        $this->assertSame(
            '1000.00',
            (string) $payment->amount
        );

        $this->assertSame(
            'paid',
            $invoice->fresh()->payment_status
        );
    }

    public function test_zero_payment_is_rejected(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Payment amount must be greater than zero.'
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '0.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-003',
            actor: $user,
        );
    }

    public function test_negative_payment_is_rejected(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $this->expectException(InvalidArgumentException::class);

        $this->paymentService->create(
            invoice: $invoice,
            amount: '-10.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-004',
            actor: $user,
        );
    }

    public function test_payment_with_more_than_two_decimal_places_is_rejected(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Payment amount may contain at most two decimal places.'
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '10.001',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-005',
            actor: $user,
        );
    }

    public function test_payment_cannot_exceed_invoice_balance(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '700.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-006',
            actor: $user,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Payment amount exceeds the outstanding invoice balance.'
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '300.01',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-007',
            actor: $user,
        );
    }

    public function test_exact_idempotent_retry_returns_existing_payment(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $first = $this->paymentService->create(
            invoice: $invoice,
            amount: '250.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-008',
            actor: $user,
        );

        $second = $this->paymentService->create(
            invoice: $invoice->fresh(),
            amount: '250.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-008',
            actor: $user,
        );

        $this->assertSame(
            $first->id,
            $second->id
        );

        $this->assertSame(
            1,
            Payment::query()
                ->where('idempotency_key', 'PAY-008')
                ->count()
        );
    }

    public function test_reusing_idempotency_key_with_different_amount_is_rejected(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '250.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-009',
            actor: $user,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Idempotency key was already used with different payment data.'
        );

        $this->paymentService->create(
            invoice: $invoice->fresh(),
            amount: '300.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-009',
            actor: $user,
        );
    }

    public function test_reusing_idempotency_key_with_different_payment_method_is_rejected(): void
    {
        $user = User::factory()->sales()->create();

        $invoice = $this->createInvoice(
            user: $user,
            totalAmount: '1000.00',
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '250.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-010',
            actor: $user,
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Idempotency key was already used with different payment data.'
        );

        $this->paymentService->create(
            invoice: $invoice->fresh(),
            amount: '250.00',
            paymentMethod: 'card',
            idempotencyKey: 'PAY-010',
            actor: $user,
        );
    }

    public function test_inactive_user_cannot_create_payment(): void
    {
        $user = User::factory()->sales()->inactive()->create();

        $invoice = $this->createInvoice(
            user: User::factory()->sales()->create(),
            totalAmount: '1000.00',
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(
            'Inactive users cannot create payments.'
        );

        $this->paymentService->create(
            invoice: $invoice,
            amount: '100.00',
            paymentMethod: 'cash',
            idempotencyKey: 'PAY-011',
            actor: $user,
        );
    }

    private function createInvoice(
        User $user,
        string $totalAmount,
        string $paymentStatus = 'unpaid',
    ): Invoice {
        $customer = Customer::factory()->create();

        return Invoice::factory()->create([
            'customer_id' => $customer->id,
            'created_by' => $user->id,
            'subtotal' => $totalAmount,
            'discount_amount' => '0.00',
            'tax_rate' => '0.00',
            'tax_amount' => '0.00',
            'total_amount' => $totalAmount,
            'payment_status' => $paymentStatus,
            'status' => 'completed',
        ]);
    }
}
