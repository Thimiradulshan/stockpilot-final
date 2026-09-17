<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class PaymentService
{

public function create(
        Invoice $invoice,
        string|int $amount,
        string $paymentMethod,
        string $idempotencyKey,
        User $actor,
    ): Payment {
        $paymentAmount = $this->positiveAmount($amount);
        $paymentMethod = $this->normalizePaymentMethod($paymentMethod);
        $idempotencyKey = $this->normalizeIdempotencyKey($idempotencyKey);

        if (! $actor->isActive()) {
            throw new LogicException(
                'Inactive users cannot create payments.'
            );
        }

        return DB::transaction(function () use (
            $invoice,
            $paymentAmount,
            $paymentMethod,
            $idempotencyKey,
            $actor,
        ): Payment {
            $lockedInvoice = Invoice::query()
                ->lockForUpdate()
                ->whereKey($invoice->getKey())
                ->first();

            if ($lockedInvoice === null) {
                throw new ModelNotFoundException(
                    'The invoice no longer exists.'
                );
            }

            if ($lockedInvoice->status !== 'completed') {
                throw new LogicException(
                    'Payments can only be recorded for completed invoices.'
                );
            }

            $existingPayment = Payment::query()
                ->where('idempotency_key', $idempotencyKey)
                ->first();

            if ($existingPayment !== null) {
                $this->assertIdempotentRetryMatches(
                    payment: $existingPayment,
                    invoiceId: $lockedInvoice->getKey(),
                    amount: $paymentAmount,
                    paymentMethod: $paymentMethod,
                );

                return $existingPayment;
            }

            $invoiceTotal = $this->decimalAmount(
                (string) $lockedInvoice->total_amount
            );

            $paidAmount = $this->decimalAmount(
                (string) Payment::query()
                    ->where('invoice_id', $lockedInvoice->getKey())
                    ->sum('amount')
            );

            $outstandingBalance = $invoiceTotal->minus($paidAmount);

            if ($outstandingBalance->isNegative()) {
                throw new LogicException(
                    'Invoice payment history is inconsistent.'
                );
            }

            if ($paymentAmount->isGreaterThan($outstandingBalance)) {
                throw new LogicException(
                    'Payment amount exceeds the outstanding invoice balance.'
                );
            }

            $payment = Payment::query()->create([
                'invoice_id' => $lockedInvoice->getKey(),
                'payment_date' => now(),
                'amount' => (string) $paymentAmount->toScale(2),
                'payment_method' => $paymentMethod,
                'reference' => null,
                'idempotency_key' => $idempotencyKey,
                'notes' => null,
                'received_by' => $actor->getKey(),
            ]);

            $newPaidAmount = $paidAmount->plus($paymentAmount);

            $lockedInvoice->payment_status = $newPaidAmount
                ->isEqualTo($invoiceTotal)
                ? 'paid'
                : 'partially_paid';

            $lockedInvoice->save();

            return $payment;
        });
    }


protected function assertIdempotentRetryMatches(
        Payment $payment,
        int $invoiceId,
        BigDecimal $amount,
        string $paymentMethod,
    ): void {
        $existingAmount = $this->decimalAmount(
            (string) $payment->amount
        );

        $sameInvoice = $payment->invoice_id === $invoiceId;

        $sameAmount = $existingAmount->isEqualTo($amount);

        $sameMethod = $payment->payment_method === $paymentMethod;

        if (! $sameInvoice || ! $sameAmount || ! $sameMethod) {
            throw new LogicException(
                'Idempotency key was already used with different payment data.'
            );
        }
    }


private function normalizePaymentMethod(
        string $paymentMethod
    ): string {
        $value = trim($paymentMethod);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Payment method is required.'
            );
        }

        if (strlen($value) > 30) {
            throw new InvalidArgumentException(
                'Payment method may not exceed 30 characters.'
            );
        }

        return $value;
    }


private function normalizeIdempotencyKey(
        string $idempotencyKey
    ): string {
        $value = trim($idempotencyKey);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Idempotency key is required.'
            );
        }

        if (strlen($value) > 100) {
            throw new InvalidArgumentException(
                'Idempotency key may not exceed 100 characters.'
            );
        }

        return $value;
    }


private function positiveAmount(string|int $amount): BigDecimal
    {
        $value = $this->decimalAmount($amount);

        if ($value->isNegative() || $value->isZero()) {
            throw new InvalidArgumentException(
                'Payment amount must be greater than zero.'
            );
        }

        return $value;
    }


private function decimalAmount(string|int $amount): BigDecimal
    {
        if (is_int($amount)) {
            return BigDecimal::of($amount)->toScale(2);
        }

        $value = trim($amount);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Payment amount is required.'
            );
        }

        if (! preg_match('/^\d+(?:\.\d+)?$/', $value)) {
            if (preg_match('/^-\d+(?:\.\d+)?$/', $value)) {
                return BigDecimal::of($value);
            }

            throw new InvalidArgumentException(
                'Payment amount must be a valid decimal number.'
            );
        }

        if (str_contains($value, '.')) {
            $decimalPart = substr(
                $value,
                strpos($value, '.') + 1
            );

            if (strlen($decimalPart) > 2) {
                throw new InvalidArgumentException(
                    'Payment amount may contain at most two decimal places.'
                );
            }
        }

        return BigDecimal::of($value)->toScale(2);
    }
}
