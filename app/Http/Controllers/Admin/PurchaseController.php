<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePurchaseRequest;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use LogicException;

class PurchaseController extends Controller
{

public function store(
        StorePurchaseRequest $request,
        PurchaseService $purchaseService,
    ): RedirectResponse {
        $this->authorize('create', Purchase::class);

        $data = $request->validated();

        $supplier = Supplier::query()
            ->whereKey($data['supplier_id'])
            ->firstOrFail();

        try {
            $purchaseService->create(
                supplier: $supplier,
                purchaseNumber: $data['purchase_number'] ?? null,
                purchaseDate: $data['purchase_date'],
                items: $data['items'],
                discountAmount: $data['discount_amount'],
                taxAmount: $data['tax_amount'],
                actor: $request->user(),
                notes: $data['notes'] ?? null,
            );
        } catch (LogicException $e) {
            Log::warning('Purchase creation rejected', [
                'purchase_number' => $data['purchase_number'] ?? null,
                'user_id' => $request->user()?->getKey(),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->with('error', $e->getMessage());
        }

        return to_route('admin.purchases.index')
            ->with('success', 'Purchase created successfully.');
    }


public function cancel(
        Purchase $purchase,
        PurchaseService $purchaseService,
    ): RedirectResponse {
        $this->authorize('cancel', $purchase);

        try {
            $purchaseService->cancel(
                purchase: $purchase,
                actor: request()->user(),
            );
        } catch (LogicException $e) {
            Log::warning('Purchase cancellation rejected', [
                'purchase_id' => $purchase->getKey(),
                'user_id' => request()->user()?->getKey(),
                'message' => $e->getMessage(),
            ]);

            return back()
                ->with('error', $e->getMessage());
        }

        return to_route('admin.purchases.index')
            ->with('success', 'Purchase cancelled successfully.');
    }
}
