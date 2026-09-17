<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

class InvoiceService
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly DocumentNumberService $documentNumbers,
    ) {}


public function create(
        Customer $customer,
        ?string $invoiceNumber,
        string $invoiceDate,
        array $items,
        string|int $discountAmount,
        string|int $taxRate,
        User $actor,
        ?string $notes = null,
        string $discountType = 'fixed',
    ): Invoice {
        $invoiceDate = $this->normalizeInvoiceDate(
            $invoiceDate
        );

        $invoiceNumber = $this->resolveInvoiceNumber(
            $invoiceNumber,
            $invoiceDate,
        );

        $discountType = $this->normalizeDiscountType($discountType);

        $discountValue = $this->nonNegativeMoney(
            $discountAmount,
            'Invoice discount amount'
        );

        if (
            $discountType === 'percent'
            && $discountValue->isGreaterThan(100)
        ) {
            throw new InvalidArgumentException(
                'Invoice discount percentage cannot exceed 100 percent.'
            );
        }

        $rate = $this->taxRate($taxRate);

        $normalizedNotes = $this->normalizeNotes($notes);

        $normalizedItems = $this->normalizeItems($items);

        if (! $actor->isActive()) {
            throw new LogicException(
                'Inactive users cannot create invoices.'
            );
        }

        return DB::transaction(function () use (
            $customer,
            $invoiceNumber,
            $invoiceDate,
            $normalizedItems,
            $discountType,
            $discountValue,
            $rate,
            $normalizedNotes,
            $actor,
        ): Invoice {
            $lockedCustomer = Customer::query()
                ->lockForUpdate()
                ->whereKey($customer->getKey())
                ->first();

            if ($lockedCustomer === null) {
                throw new ModelNotFoundException(
                    'The customer no longer exists.'
                );
            }

            if (! $lockedCustomer->isActive()) {
                throw new LogicException(
                    'Inactive customers cannot be used for invoices.'
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
                    'One or more invoice products no longer exist.'
                );
            }

            foreach ($normalizedItems as $item) {
                $product = $products->get($item['product_id']);

                if (! $product instanceof Product) {
                    throw new ModelNotFoundException(
                        'An invoice product could not be loaded.'
                    );
                }

                if (! $product->isActive()) {
                    throw new LogicException(
                        'Inactive products cannot be sold.'
                    );
                }
            }

            $subtotal = BigDecimal::zero();

            foreach ($normalizedItems as &$item) {
                $lineTotal = $this->calculateLineTotal($item);

                $item['line_total'] = $lineTotal;

                $subtotal = $subtotal->plus(
                    $this->calculateGoodsSubtotal($item)
                );
            }

            unset($item);

            $headerDiscount = $discountType === 'percent'
                ? $subtotal
                    ->multipliedBy($discountValue)
                    ->dividedBy(100, 2, RoundingMode::HalfUp)
                : $discountValue;

            if ($headerDiscount->isGreaterThan($subtotal)) {
                throw new LogicException(
                    'Invoice discount cannot exceed the invoice subtotal.'
                );
            }

            $taxAmount = $this->calculateTaxAmount(
                $subtotal,
                $rate
            );

            $totalAmount = $subtotal
                ->minus($headerDiscount)
                ->plus($taxAmount);

            if ($totalAmount->isNegative()) {
                throw new LogicException(
                    'Invoice total cannot be negative.'
                );
            }

            $invoice = $this->createInvoiceHeader(
                invoiceNumber: $invoiceNumber,
                lockedCustomerId: $lockedCustomer->getKey(),
                invoiceDate: $invoiceDate,
                subtotal: $subtotal,
                headerDiscount: $headerDiscount,
                rate: $rate,
                taxAmount: $taxAmount,
                totalAmount: $totalAmount,
                normalizedNotes: $normalizedNotes,
                actor: $actor,
            );

            usort(
                $normalizedItems,
                fn (array $left, array $right): int => $left['product_id'] <=> $right['product_id']
            );

            foreach ($normalizedItems as $item) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->getKey(),
                    'product_id' => $item['product_id'],
                    'quantity' => (string) $item['quantity']->toScale(3),
                    'unit_price' => (string) $item['unit_price']->toScale(2),
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
                        'An invoice product could not be loaded.'
                    );
                }

                $this->stockService->decrease(
                    product: $product,
                    quantity: (string) $item['quantity']->toScale(3),
                    actor: $actor,
                    movementType: StockMovementType::SALE,
                    referenceType: 'invoice',
                    referenceId: $invoice->getKey(),
                    reason: 'Sale recorded.',
                );
            }

            return $invoice->load('items');
        });
    }


public function void(
        Invoice $invoice,
        User $actor,
    ): Invoice {
        if (! $actor->isActive()) {
            throw new LogicException(
                'Inactive users cannot void invoices.'
            );
        }

        return DB::transaction(function () use (
            $invoice,
            $actor,
        ): Invoice {
            $lockedInvoice = Invoice::query()
                ->lockForUpdate()
                ->whereKey($invoice->getKey())
                ->first();

            if ($lockedInvoice === null) {
                throw new ModelNotFoundException(
                    'The invoice no longer exists.'
                );
            }

            if ($lockedInvoice->status === 'voided') {
                throw new LogicException(
                    'The invoice has already been voided.'
                );
            }

            if ($lockedInvoice->status !== 'completed') {
                throw new LogicException(
                    'Only completed invoices can be voided.'
                );
            }

            if ($lockedInvoice->payment_status !== 'unpaid') {
                throw new LogicException(
                    'An invoice with recorded payments cannot be voided.'
                );
            }

            $hasRecordedPayments = Payment::query()
                ->where('invoice_id', $lockedInvoice->getKey())
                ->exists();

            if ($hasRecordedPayments) {
                throw new LogicException(
                    'An invoice with recorded payments cannot be voided.'
                );
            }

            $items = InvoiceItem::query()
                ->where('invoice_id', $lockedInvoice->getKey())
                ->orderBy('product_id')
                ->orderBy('id')
                ->get();

            if ($items->isEmpty()) {
                throw new LogicException(
                    'An invoice without items cannot be voided.'
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
                    'One or more invoice products no longer exist.'
                );
            }

            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if (! $product instanceof Product) {
                    throw new ModelNotFoundException(
                        'An invoice product could not be loaded.'
                    );
                }

                if (! $product->isActive()) {
                    throw new LogicException(
                        'Inactive products cannot be used to reverse an invoice.'
                    );
                }

                $this->stockService->increase(
                    product: $product,
                    quantity: (string) $item->quantity,
                    actor: $actor,
                    movementType: StockMovementType::RETURN,
                    referenceType: 'invoice',
                    referenceId: $lockedInvoice->getKey(),
                    reason: 'Invoice voided.',
                );
            }

            $lockedInvoice->status = 'voided';
            $lockedInvoice->save();

            return $lockedInvoice->load('items');
        });
    }


private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw new InvalidArgumentException(
                'An invoice must contain at least one item.'
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
                    "Invoice item {$index} has an invalid product ID."
                );
            }

            $productId = filter_var(
                $item['product_id'],
                FILTER_VALIDATE_INT
            );

            if ($productId === false || $productId <= 0) {
                throw new InvalidArgumentException(
                    "Invoice item {$index} has an invalid product ID."
                );
            }

            if (in_array($productId, $productIds, true)) {
                throw new InvalidArgumentException(
                    'A product cannot appear more than once in the same invoice.'
                );
            }

            $productIds[] = $productId;

            $quantity = $this->positiveQuantity(
                $item['quantity'] ?? null
            );

            $unitPrice = $this->positiveOrZeroMoney(
                $item['unit_price'] ?? null,
                'Invoice unit price'
            );

            if ($unitPrice->isZero()) {
                throw new InvalidArgumentException(
                    'Invoice unit price must be greater than zero.'
                );
            }

            $itemDiscount = $this->nonNegativeMoney(
                $item['discount_amount'] ?? '0',
                'Invoice item discount amount'
            );

            $itemTax = $this->nonNegativeMoney(
                $item['tax_amount'] ?? '0',
                'Invoice item tax amount'
            );

            $gross = $quantity->multipliedBy($unitPrice);

            if ($itemDiscount->isGreaterThan($gross)) {
                throw new LogicException(
                    'Invoice item discount cannot exceed the item gross amount.'
                );
            }

            if ($itemTax->isGreaterThan($gross)) {
                throw new LogicException(
                    'Invoice item tax cannot exceed the item gross amount.'
                );
            }

            $lineTotal = $gross
                ->minus($itemDiscount)
                ->plus($itemTax)
                ->toScale(2);

            if ($lineTotal->isNegative()) {
                throw new LogicException(
                    'Invoice item total cannot be negative.'
                );
            }

            $normalized[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
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
            ->multipliedBy($item['unit_price'])
            ->minus($item['discount_amount'])
            ->plus($item['tax_amount'])
            ->toScale(2);
    }


private function calculateGoodsSubtotal(array $item): BigDecimal
    {
        return $item['quantity']
            ->multipliedBy($item['unit_price'])
            ->toScale(2);
    }


private function calculateTaxAmount(
        BigDecimal $subtotal,
        BigDecimal $rate,
    ): BigDecimal {
        return $subtotal
            ->multipliedBy($rate)
            ->dividedBy(100, 2, RoundingMode::HalfUp);
    }


private function positiveQuantity(mixed $quantity): BigDecimal
    {
        if (! is_int($quantity) && ! is_string($quantity)) {
            throw new InvalidArgumentException(
                'Invoice item quantity must be a valid decimal number.'
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
                'Invoice item quantity must be a valid decimal number.'
            );
        }

        if (str_contains($value, '.')) {
            $decimalPart = substr(
                $value,
                strpos($value, '.') + 1
            );

            if (strlen($decimalPart) > 3) {
                throw new InvalidArgumentException(
                    'Invoice item quantity may contain at most three decimal places.'
                );
            }
        }

        $amount = BigDecimal::of($value);

        if ($amount->isZero()) {
            throw new InvalidArgumentException(
                'Invoice item quantity must be greater than zero.'
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


private function taxRate(string|int $rate): BigDecimal
    {
        $value = $this->decimalMoney($rate, 'Invoice tax rate');

        if ($value->isNegative()) {
            throw new InvalidArgumentException(
                'Invoice tax rate cannot be negative.'
            );
        }

        if ($value->isGreaterThan(100)) {
            throw new InvalidArgumentException(
                'Invoice tax rate cannot exceed 100 percent.'
            );
        }

        return $value;
    }


private function createInvoiceHeader(
        string $invoiceNumber,
        int $lockedCustomerId,
        string $invoiceDate,
        BigDecimal $subtotal,
        BigDecimal $headerDiscount,
        BigDecimal $rate,
        BigDecimal $taxAmount,
        BigDecimal $totalAmount,
        ?string $normalizedNotes,
        User $actor,
    ): Invoice {
        if (
            Invoice::query()
                ->where('invoice_number', $invoiceNumber)
                ->lockForUpdate()
                ->exists()
        ) {
            throw new LogicException(
                'An invoice with this number already exists.'
            );
        }

        try {
            return Invoice::query()->create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $lockedCustomerId,
                'invoice_date' => $invoiceDate,
                'subtotal' => (string) $subtotal->toScale(2),
                'discount_amount' => (string) $headerDiscount->toScale(2),
                'tax_rate' => (string) $rate->toScale(2),
                'tax_amount' => (string) $taxAmount->toScale(2),
                'total_amount' => (string) $totalAmount->toScale(2),
                'status' => 'completed',
                'payment_status' => 'unpaid',
                'notes' => $normalizedNotes,
                'created_by' => $actor->getKey(),
            ]);
        } catch (QueryException $e) {
            if (str_contains((string) $e->getCode(), '23000')) {
                throw new LogicException(
                    'An invoice with this number already exists.'
                );
            }

            throw $e;
        }
    }


private function normalizeNotes(?string $notes): ?string
    {
        if ($notes === null) {
            return null;
        }

        $value = trim($notes);

        return $value === '' ? null : $value;
    }


private function normalizeDiscountType(string $discountType): string
    {
        $value = strtolower(trim($discountType));

        if (! in_array($value, ['fixed', 'percent'], true)) {
            throw new InvalidArgumentException(
                'Invoice discount type must be either fixed or percent.'
            );
        }

        return $value;
    }


private function resolveInvoiceNumber(
        ?string $invoiceNumber,
        string $invoiceDate,
    ): string {
        $value = trim((string) $invoiceNumber);

        if ($value === '') {
            return $this->documentNumbers->next(
                'invoice',
                $invoiceDate,
            );
        }

        if (strlen($value) > 50) {
            throw new InvalidArgumentException(
                'Invoice number may not exceed 50 characters.'
            );
        }

        return $value;
    }


private function normalizeInvoiceDate(
        string $invoiceDate
    ): string {
        $value = trim($invoiceDate);

        if ($value === '') {
            throw new InvalidArgumentException(
                'Invoice date is required.'
            );
        }

        try {
            $date = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $value
            );
        } catch (\Throwable) {
            throw new InvalidArgumentException(
                'Invoice date must use the YYYY-MM-DD format.'
            );
        }

        if (
            ! $date instanceof CarbonImmutable
            || $date->format('Y-m-d') !== $value
        ) {
            throw new InvalidArgumentException(
                'Invoice date must be a valid calendar date.'
            );
        }

        $this->assertDateWithinWindow(
            $date,
            'Invoice'
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
