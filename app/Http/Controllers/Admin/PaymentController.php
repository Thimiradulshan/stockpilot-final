<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LogicException;

class PaymentController extends Controller
{

public function store(
        StorePaymentRequest $request,
        Invoice $invoice,
        PaymentService $paymentService,
    ): RedirectResponse {
        $this->authorize('pay', $invoice);
        $this->authorize('create', Payment::class);

        $data = $request->validated();

        try {
            $paymentService->create(
                invoice: $invoice,
                amount: $data['amount'],
                paymentMethod: $data['payment_method'],
                idempotencyKey: $data['idempotency_key'] ?? Str::uuid(),
                actor: $request->user(),
            );
        } catch (LogicException $e) {
            Log::warning('Payment rejected', [
                'invoice_id' => $invoice->getKey(),
                'user_id' => $request->user()?->getKey(),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->with('error', $e->getMessage());
        }

        return to_route('admin.sales.index')
            ->with('success', 'Payment recorded successfully.');
    }
}
