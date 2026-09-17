<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class PurchaseService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly DocumentNumberService $documentNumbers,
    ) {}


public function create(
        Supplier $supplier,
        ?string $purchaseNumber,
        string $purchaseDate,
        array $items,
        string|int $discountAmount,
        string|int $taxAmount,
        User $actor,
        ?string $notes = null,
    ): Purchase {
        $purchaseDate = $this->normalizePurchaseDate(
            $purchaseDate
        );

        $purchaseNumber = $this->resolvePurchaseNumber(
            $purchaseNumber,
            $purchaseDate,
        );

        $headerDiscount = $this->nonNegativeMoney(
            $discountAmount,
            'Purchase discount amount'
        );

        $headerTax = $this->nonNegativeMoney(
            $taxAmount,
            'Purchase tax amount'
        );

        $normalizedNotes = $this->normalizeNotes($notes);

        $normalizedItems = $this->normalizeItems($items);

        if (! $actor->isActive()) {
            throw new LogicException(
                'Inactive users cannot create purchases.'
            );
        }

        return DB::transaction(function () use (
            $supplier,
            $purchaseNumber,
            $purchaseDate,
            $normalizedItems,
            $headerDiscount,
            $headerTax,
            $normalizedNotes,
            $actor,
        ): Purchase {
            $lockedSupplier = Supplier::query()
                ->lockForUpdate()
                ->whereKey($supplier->getKey())
                ->first();

            if ($lockedSupplier === null) {
                throw new ModelNotFoundException(
                    'The supplier no longer exists.'
                );
            }

            if (! $lockedSupplier->isActive()) {
                throw new LogicException(
                    'Inactive suppliers cannot be used for purchases.'
                );
            }

            $productIds = array_column(
                $normalizedItems,
                'product_id'
            );

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw new ModelNotFoundException(
                    'One or more purchase products no longer exist.'
                );
            }

            foreach ($normalizedItems as $item) {
                $product = $products->get($item['product_id']);

                if (! $product instanceof Product) {
                    throw new ModelNotFoundException(
                        'A purchase product could not be loaded.'
                    );
                }

                if (! $product->isActive()) {
                    throw new LogicException(
                        'Inactive products cannot be purchased.'
                    );
                }
            }

            $subtotal = BigDecimal::zero();

            foreach ($normalizedItems as &$item) {
                $lineTotal = $this->calculateLineTotal($item);

                $item['line_total'] = $lineTotal;

                $subtotal = $subtotal->plus($lineTotal);
            }

            unset($item);

            if ($headerDiscount->isGreaterThan($subtotal)) {
                throw new LogicException(
                    'Purchase discount cannot exceed the purchase subtotal.'
                );
            }

            $totalAmount = $subtotal
                ->minus($headerDiscount)
                ->plus($headerTax);

            if ($totalAmount->isNegative()) {
                throw new LogicException(
                    'Purchase total cannot be negative.'
                );
            }

            $purchase = $this->createPurchaseHeader(
                purchaseNumber: $purchaseNumber,
                lockedSupplierId: $lockedSupplier->getKey(),
                purchaseDate: $purchaseDate,
                subtotal: $subtotal,
                headerDiscount: $headerDiscount,
                headerTax: $headerTax,
                totalAmount: $totalAmount,
                normalizedNotes: $normalizedNotes,
                actor: $actor,
            );

            usort(
                $normalizedItems,
                fn (array $left, array $right): int => $left['product_id'] <=> $right['product_id']
            );

            foreach ($normalizedItems as $item) {
                PurchaseItem::query()->create([
                    'purchase_id' => $purchase->getKey(),
                    'product_id' => $item['product_id'],
                    'quantity' => (string) $item['quantity']->toScale(3),
                    'unit_cost' => (string) $item['unit_cost']->toScale(2),
                    'discount_amount' => (string) $item['discount_amount']
                        ->toScale(2),
                    'tax_amount' => (string) $item['tax_amount']
                        ->toScale(2),
                    'line_total' => (string) $item['line_total']
                        ->toScale(2),
                ]);

                $product = $products->get($item['product_id']);

                if (! $product instanceof Product) {
                    throw new ModelNotFoundException(
                        'A purchase product could not be loaded.'
                    );
                }

                $this->stockService->increase(
                    product: $product,
                    quantity: (string) $item['quantity']->toScale(3),
                    actor: $actor,
                    movementType: StockMovementType::PURCHASE,
                    referenceType: 'purchase',
                    referenceId: $purchase->getKey(),
                    reason: 'Purchase received.',
                );
            }

            return $purchase->load('items');
        });
    }


public function cancel(
        Purchase $purchase,
        User $actor,
    ): Purchase {
        if (! $actor->isActive()) {
            throw new LogicException(
                'Inactive users cannot cancel purchases.'
            );
        }

        return DB::transaction(function () use (
            $purchase,
            $actor,
        ): Purchase {
            $lockedPurchase = Purchase::query()
                ->lockForUpdate()
                ->whereKey($purchase->getKey())
                ->first();

            if ($lockedPurchase === null) {
                throw new ModelNotFoundException(
                    'The purchase no longer exists.'
                );
            }

            if ($lockedPurchase->status === 'cancelled') {
                throw new LogicException(
                    'The purchase has already been cancelled.'
                );
            }

            if ($lockedPurchase->status !== 'completed') {
                throw new LogicException(
                    'Only completed purchases can be cancelled.'
                );
            }

            $items = PurchaseItem::query()
                ->where('purchase_id', $lockedPurchase->getKey())
                ->orderBy('product_id')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                throw new LogicException(
                    'A purchase without items cannot be cancelled.'
                );
            }

            $productIds = $items
                ->pluck('product_id')
                ->unique()
                ->sort()
                ->values()
                ->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($productIds)) {
                throw new ModelNotFoundException(
                    'One or more purchase products no longer exist.'
                );
            }

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product instanceof Product) {
                    throw new ModelNotFoundException(
                        'A purchase product could not be loaded.'
                    );
                }

                if (! $product->isActive()) {
                    throw new LogicException(
                        'Inactive products cannot be used to reverse a purchase.'
                    );
                }

                $currentQuantity = $this->decimalQuantity(
                    (string) $product->quantity
                );

                $purchaseQuantity = $this->decimalQuantity(
                    (string) $item->quantity
                );

                if ($currentQuantity->isLessThan($purchaseQuantity)) {
                    throw new LogicException(
                        'Purchase cancellation would make stock negative for product.'
                    );
                }
            }

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product instanceof Product) {
                    throw new ModelNotFoundException(
                        'A purchase product could not be loaded.'
                    );
                }

                $this->stockService->decrease(
                    product: $product,
                    quantity: (string) $item->quantity,
                    actor: $actor,
                    movementType: StockMovementType::CORRECTION,
                    referenceType: 'purchase',
                    referenceId: $lockedPurchase->getKey(),
                    reason: 'Purchase cancelled.',
                );
            }

            $lockedPurchase->status = 'cancelled';
            $lockedPurchase->save();

            return $lockedPurchase->load('items');
        });
    }


private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException(
                'A purchase must contain at least one item.'
            );
        }

        $normalized = [];
        $productIds = [];

        foreach ($items as $index => $item) {
            if (
                ! array_key_exists('product_id', $item)
                || ! is_numeric($item['product_id'])
            ) {
                throw new InvalidArgumentException(
                    "Purchase item {$index} has an invalid product ID."
                );
            }

            $productId = filter_var(
                $item['product_id'],
                FILTER_VALIDATE_INT
            );

            if ($productId === false || $productId <= 0) {
                throw new InvalidArgumentException(
                    "Purchase item {$index} has an invalid product ID."
                );
            }

            if (in_array($productId, $productIds, true)) {
                throw new InvalidArgumentException(
                    'A product cannot appear more than once in the same purchase.'
                );
            }

            $productIds[] = $productId;

            $quantity = $this->positiveQuantity(
                $item['quantity'] ?? null
            );

            $unitCost = $this->positiveOrZeroMoney(
                $item['unit_cost'] ?? null,
                'Purchase unit cost'
            );

            if ($unitCost->isZero()) {
                throw new InvalidArgumentException(
                    'Purchase unit cost must be greater than zero.'
                );
            }

            $itemDiscount = $this->nonNegativeMoney(
                $item['discount_amount'] ?? '0',
                'Purchase item discount amount'
            );

            $itemTax = $this->nonNegativeMoney(
                $item['tax_amount'] ?? '0',
                'Purchase item tax amount'
            );

            $gross = $quantity->multipliedBy($unitCost);

            if ($itemDiscount->isGreaterThan($gross)) {
                throw new LogicException(
                    'Purchase item discount cannot exceed the item gross amount.'
                );
            }

            if ($itemTax->isGreaterThan($gross)) {
                throw new LogicException(
                    'Purchase item tax cannot exceed the item gross amount.'
                );
            }

            $lineTotal = $gross
                ->minus($itemDiscount)
                ->plus($itemTax)
                ->toScale(2);

            if ($lineTotal->isNegative()) {
                throw new LogicException(
                    'Purchase item total cannot be negative.'
                );
            }

            $normalized[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'discount_amount' => $itemDiscount,
                'tax_amount' => $itemTax,
                'line_total' => $lineTotal,
            ];
        }

        return $normalized;
    }


private function calculateLineTotal(array $item): BigDecimal
    {
        return $item['quantity']
            ->multipliedBy($item['unit_cost'])
            ->minus($item['discount_amount'])
            ->plus($item['tax_amount'])
            ->toScale(2);
    }


private function positiveQuantity(mixed $quantity): BigDecimal
    {
        if (! is_int($quantity) && ! is_string($quantity)) {
            throw new InvalidArgumentException(
                'Purchase item quantity must be a valid decimal number.'
            );
        }

        $value = is_int($quantity)
            ? (string) $quantity
            : trim($quantity);

        if (
            $value === ''
            || ! preg_match('/^\d+(?:\.\d+)?$/', $value)
        ) {
            throw new InvalidArgumentException(
                'Purchase item quantity must be a valid decimal number.'
            );
        }

        if (str_contains($value, '.')) {
            $decimalPart = substr(
                $value,
                strpos($value, '.') + 1
            );

            if (strlen($decimalPart) > 3) {
                throw new InvalidArgumentException(
                    'Purchase item quantity may contain at most three decimal places.'
                );
            }
        }

        $amount = BigDecimal::of($value);

        if ($amount->isZero()) {
            throw new InvalidArgumentException(
                'Purchase item quantity must be greater than zero.'
            );
        }

        return $amount;
    }


private function nonNegativeMoney(
        string|int $amount,
        string $field,
    ): BigDecimal {
        $value = $this->decimalMoney($amount, $field);

        if ($value->isNegative()) {
            throw new InvalidArgumentException(
                "{$field} cannot be negative."
            );
        }

        return $value;
    }


private function positiveOrZeroMoney(
        string|int|null $amount,
        string $field,
    ): BigDecimal {
        if ($amount === null) {
            throw new InvalidArgumentException(
                "{$field} is required."
            );
        }

        return $this->nonNegativeMoney(
            $amount,
            $field
        );
    }


private function decimalMoney(
        string|int $amount,
        string $field,
    ): BigDecimal {
        $value = is_int($amount)
            ? (string) $amount
            : trim($amount);

        if (
            $value === ''
            || ! preg_match(
                '/^-?\d+(?:\.\d+)?$/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                "{$field} must be a valid decimal number."
            );
        }

        if (str_contains($value, '.')) {
            $decimalPart = substr(
                $value,
                strpos($value, '.') + 1
            );

            if (strlen($decimalPart) > 2) {
                throw new InvalidArgumentException(
                    "{$field} may contain at most two decimal places."
                );
            }
        }

        return BigDecimal::of($value)->toScale(2);
    }


private function normalizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        $value = trim($notes);

        return $value === '' ? null : $value;
    }


private function decimalQuantity(string $quantity): BigDecimal
    {
        $value = trim($quantity);

        if (
            $value === ''
            || ! preg_match(
                '/^-?\d+(?:\.\d+)?$/',
                $value
            )
        ) {
            throw new InvalidArgumentException(
                'Product stock quantity is invalid.'
            );
        }

        return BigDecimal::of($value)->toScale(3);
    }


private function resolvePurchaseNumber(
        ?string $purchaseNumber,
        string $purchaseDate,
    ): string {
        $value = trim((string) $purchaseNumber);

        if ($value === '') {
            return $this->documentNumbers->next(
                'purchase',
                $purchaseDate,
            );
        }

        if (strlen($value) > 50) {
            throw new InvalidArgumentException(
                'Purchase number may not exceed 50 characters.'
            );
        }

        return $value;
    }


private function createPurchaseHeader(
        string $purchaseNumber,
        int $lockedSupplierId,
        string $purchaseDate,
        BigDecimal $subtotal,
        BigDecimal $headerDiscount,
        BigDecimal $headerTax,
        BigDecimal $totalAmount,
        ?string $normalizedNotes,
        User $actor,
    ): Purchase {
        if (
            Purchase::query()
                ->where('purchase_number', $purchaseNumber)
                ->lockForUpdate()
                ->exists()
        ) {
            throw new LogicException(
                'A purchase with this number already exists.'
            );
        }

        try {
            return Purchase::query()->create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $lockedSupplierId,
                'purchase_date' => $purchaseDate,
                'subtotal' => (string) $subtotal->toScale(2),
                'discount_amount' => (string) $headerDiscount->toScale(2),
                'tax_amount' => (string) $headerTax->toScale(2),
                'total_amount' => (string) $totalAmount->toScale(2),
                'status' => 'completed',
                'notes' => $normalizedNotes,
                'created_by' => $actor->getKey(),
            ]);
        } catch (QueryException $e) {
            if (str_contains((string) $e->getCode(), '23000')) {
                throw new LogicException(
                    'A purchase with this number already exists.'
                );
            }

            throw $e;
        }
    }


private function normalizePurchaseDate(
        string $purchaseDate
    ): string {
        $value = trim($purchaseDate);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Purchase date is required.'
            );
        }

        try {
            $date = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $value
            );
        } catch (\Throwable) {
            throw new InvalidArgumentException(
                'Purchase date must use the YYYY-MM-DD format.'
            );
        }

        if (
            ! $date instanceof CarbonImmutable
            || $date->format('Y-m-d') !== $value
        ) {
            throw new InvalidArgumentException(
                'Purchase date must be a valid calendar date.'
            );
        }

        $this->assertDateWithinWindow(
            $date,
            'Purchase'
        );

        return $date->format('Y-m-d');
    }


private function assertDateWithinWindow(
        CarbonImmutable $date,
        string $document,
    ): void {
        $today = CarbonImmutable::today();

        if ($date->isAfter($today)) {
            throw new InvalidArgumentException(
                "{$document} date cannot be in the future."
            );
        }

        $minimum = $today->subDays(
            (int) config('stockpilot.document_date_lookback_days')
        );

        if ($date->isBefore($minimum)) {
            throw new InvalidArgumentException(
                "{$document} date is older than the allowed look-back window."
            );
        }
    }
}
