<?php

namespace App\Livewire\Admin\Pos;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use LogicException;

class Index extends Component
{
    public string $search = '';

    public string $customerSearch = '';

    public int $customerId = 0;

    public int $walkInCustomerId = 0;

    public int $categoryId = 0;


public array $cart = [];

    public string $discountType = 'fixed';

    public string $discountAmount = '0.00';

    public string $taxRate = '0.00';

    public string $paymentMethod = 'cash';

    public string $tendered = '0.00';

    public string $idempotencyKey = '';

    public bool $showQuickCustomer = false;

    public string $quickName = '';

    public string $quickPhone = '';

    public string $quickEmail = '';

    public string $notice = '';


public ?array $successSale = null;

    public function mount(): void
    {
        $this->authorize('create', Invoice::class);
        $this->authorize('create', Payment::class);

        $walkIn = $this->ensureWalkInCustomer();

        $this->walkInCustomerId = (int) $walkIn->getKey();

        $this->resetSale();
    }

    public function resetSale(): void
    {
        $this->cart = [];
        $this->discountType = 'fixed';
        $this->discountAmount = '0.00';
        $this->taxRate = '0.00';
        $this->paymentMethod = 'cash';
        $this->tendered = '0.00';
        $this->idempotencyKey = (string) Str::uuid();
        $this->notice = '';
        $this->successSale = null;
        $this->customerSearch = '';
        $this->categoryId = 0;
        $this->customerId = $this->walkInCustomerId;
    }

    public function updatedSearch(): void
    {
        $this->notice = '';
    }

    public function updatedCustomerSearch(): void
    {
        $this->notice = '';
    }

    public function selectCustomer(int $customerId): void
    {
        $customer = Customer::query()
            ->whereKey($customerId)
            ->where('status', 'active')
            ->first();

        if (! $customer instanceof Customer) {
            $this->notice = 'That customer is not available.';

            return;
        }

        $this->customerId = (int) $customer->getKey();
        $this->customerSearch = '';
        $this->notice = '';
    }

    public function useWalkInCustomer(): void
    {
        $this->customerId = $this->walkInCustomerId;
        $this->customerSearch = '';
        $this->notice = '';
    }

    public function clearCustomer(): void
    {
        $this->useWalkInCustomer();
    }

    public function selectCategory(int $categoryId): void
    {
        $this->categoryId = max(0, $categoryId);
    }

    public function addProduct(int $productId): void
    {
        $product = Product::query()
            ->whereKey($productId)
            ->where('status', 'active')
            ->first();

        if (! $product instanceof Product) {
            $this->notice = 'The selected product is no longer available.';

            return;
        }

        if ((float) $product->quantity <= 0) {
            $this->notice = "{$product->name} is out of stock.";

            return;
        }

        $existing = $this->cart[$productId] ?? null;

        if ($existing !== null) {
            $this->cart[$productId]['quantity'] = (string) round(
                (float) $existing['quantity'] + 1,
                3,
            );
        } else {
            $this->cart[$productId] = [
                'quantity' => '1',
                'unit_price' => number_format(
                    round((float) $product->selling_price, 2),
                    2,
                    '.',
                    '',
                ),
            ];
        }

        $this->notice = '';
    }

    public function incrementQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]['quantity'] = (string) round(
            (float) $this->cart[$productId]['quantity'] + 1,
            3,
        );

        $this->notice = '';
    }

    public function decrementQuantity(int $productId): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        $quantity = (float) $this->cart[$productId]['quantity'];

        if ($quantity <= 1) {
            unset($this->cart[$productId]);

            return;
        }

        $this->cart[$productId]['quantity'] = (string) round(
            $quantity - 1,
            3,
        );

        $this->notice = '';
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);

        $this->notice = '';
    }

    public function clearCart(): void
    {
        $this->cart = [];

        $this->notice = '';
    }

    public function setPaymentMethod(string $method): void
    {
        if (! in_array($method, ['cash', 'card', 'bank_transfer', 'credit'], true)) {
            return;
        }

        $this->paymentMethod = $method;
    }

    public function tenderedExact(): void
    {
        $this->tendered = number_format(
            $this->totalAmount(),
            2,
            '.',
            '',
        );
    }

    public function tenderedAdd(float $amount): void
    {
        $this->tendered = number_format(
            max(0.0, $this->toFloat($this->tendered) + $amount),
            2,
            '.',
            '',
        );
    }

    public function showQuickCustomer(): void
    {
        $this->showQuickCustomer = true;
    }

    public function hideQuickCustomer(): void
    {
        $this->showQuickCustomer = false;
        $this->quickName = '';
        $this->quickPhone = '';
        $this->quickEmail = '';
    }

    public function saveQuickCustomer(): void
    {
        $this->authorize('create', Customer::class);

        try {
            $validated = $this->validate([
                'quickName' => [
                    'required',
                    'string',
                    'max:150',
                ],
                'quickPhone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
                'quickEmail' => [
                    'nullable',
                    'email',
                    'max:150',
                ],
            ], [], [
                'quickName' => 'customer name',
                'quickPhone' => 'phone number',
                'quickEmail' => 'email address',
            ]);
        } catch (ValidationException $e) {
            $this->toast('error', 'Customer not saved', 'Please check the customer details.');

            throw $e;
        }

        $customer = Customer::query()->create([
            'name' => $validated['quickName'],
            'phone' => $validated['quickPhone'] ?? null,
            'email' => $validated['quickEmail'] ?? null,
            'address' => null,
            'status' => 'active',
        ]);

        $this->customerId = (int) $customer->getKey();

        $this->notice = "Customer {$customer->name} added.";

        $this->showQuickCustomer = false;
        $this->quickName = '';
        $this->quickPhone = '';
        $this->quickEmail = '';

        $this->toast('success', 'Customer added', $customer->name.' is now selected.');
    }

    public function completeSale(
        InvoiceService $invoiceService,
        PaymentService $paymentService,
    ): void {
        $this->authorize('create', Invoice::class);
        $this->authorize('create', Payment::class);

        if ($this->successSale !== null) {
            $this->toast('error', 'Sale already completed', 'Please start a new sale for another transaction.');

            return;
        }

        try {
            $this->validate([
                'customerId' => [
                    'required',
                    'integer',
                    Rule::exists(Customer::class, 'id')
                        ->where(
                            fn ($query) => $query->where(
                                'status',
                                'active'
                            )
                        ),
                ],

                'cart' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'cart.*.quantity' => [
                    'required',
                    'decimal:0,3',
                    'gt:0',
                    'max:999999999999.999',
                ],

                'cart.*.unit_price' => [
                    'required',
                    'decimal:0,2',
                    'gt:0',
                    'max:999999999999.99',
                ],

                'discountAmount' => [
                    'nullable',
                    'decimal:0,2',
                    'min:0',
                    'max:999999999999.99',
                ],

                'taxRate' => [
                    'nullable',
                    'decimal:0,2',
                    'min:0',
                    'max:100',
                ],

                'paymentMethod' => [
                    'required',
                    'string',
                    Rule::in(['cash', 'card', 'bank_transfer', 'credit']),
                ],

                'tendered' => [
                    'required',
                    'decimal:0,2',
                    'gt:0',
                    'max:999999999999.99',
                ],
            ]);
        } catch (ValidationException $e) {
            $this->toast('error', 'Sale could not be completed', 'Please review the highlighted fields.');

            throw $e;
        }

        $customer = Customer::query()
            ->whereKey($this->customerId)
            ->first();

        if (! $customer instanceof Customer || ! $customer->isActive()) {
            $message = 'Select an active customer to complete the sale.';

            $this->addError('sale', $message);
            $this->toast('error', 'Sale could not be completed', $message);

            return;
        }

        foreach ($this->cart as $productId => $line) {
            $product = Product::query()
                ->whereKey($productId)
                ->where('status', 'active')
                ->first();

            if (! $product instanceof Product) {
                $message = 'A product in the cart is no longer available.';

                $this->addError('sale', $message);
                $this->toast('error', 'Sale could not be completed', $message);

                return;
            }

            if ((float) $product->quantity < (float) $line['quantity']) {
                $message = "Insufficient stock for {$product->name}.";

                $this->addError('sale', $message);
                $this->toast('error', 'Not enough stock', $message);

                return;
            }
        }

        $items = [];

        foreach ($this->cart as $productId => $line) {
            $items[] = [
                'product_id' => $productId,
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_amount' => '0',
                'tax_amount' => '0',
            ];
        }

        $discountAmount = trim($this->discountAmount) === ''
            ? '0.00'
            : $this->discountAmount;

        $taxRate = trim($this->taxRate) === ''
            ? '0.00'
            : $this->taxRate;

        $user = auth()->user();

        try {
            $invoice = $invoiceService->create(
                customer: $customer,
                invoiceNumber: null,
                invoiceDate: now()->toDateString(),
                items: $items,
                discountAmount: $discountAmount,
                taxRate: $taxRate,
                actor: $user,
                notes: 'Point-of-sale transaction.',
                discountType: $this->discountType,
            );
        } catch (LogicException $e) {
            $message = $e->getMessage();

            $this->addError('sale', $message);
            $this->notice = $message;
            $this->toast('error', 'Sale could not be completed', $message);

            return;
        }

        $total = (float) $invoice->total_amount;
        $tendered = $this->toFloat($this->tendered);
        $paid = min($tendered, $total);
        $change = max(0.0, $tendered - $total);

        try {
            $payment = $paymentService->create(
                invoice: $invoice,
                amount: number_format($paid, 2, '.', ''),
                paymentMethod: $this->paymentMethod,
                idempotencyKey: $this->idempotencyKey,
                actor: $user,
            );
        } catch (LogicException $e) {
            $message = $e->getMessage();

            $this->addError('sale', $message);
            $this->notice = $message;
            $this->toast('error', 'Payment could not be recorded', $message);

            return;
        }

        $invoice->refresh();

        $this->successSale = [
            'invoice_id' => $invoice->getKey(),
            'invoice_number' => $invoice->invoice_number,
            'total' => number_format($total, 2, '.', ''),
            'paid' => number_format($paid, 2, '.', ''),
            'change' => number_format($change, 2, '.', ''),
            'payment_method' => $payment->payment_method,
            'payment_status' => $invoice->payment_status,
            'item_count' => count($items),
        ];

        $this->cart = [];
        $this->discountType = 'fixed';
        $this->discountAmount = '0.00';
        $this->taxRate = '0.00';
        $this->paymentMethod = 'cash';
        $this->tendered = '0.00';
        $this->idempotencyKey = (string) Str::uuid();
        $this->notice = '';
        $this->showQuickCustomer = false;

        $this->toast(
            'success',
            'Sale completed',
            "Invoice {$invoice->invoice_number} saved.",
        );
    }

    public function subtotalAmount(): float
    {
        $subtotal = 0.0;

        foreach ($this->cart as $line) {
            $subtotal += $this->toFloat($line['quantity'])
                * $this->toFloat($line['unit_price']);
        }

        return round($subtotal, 2);
    }

    public function taxAmount(): float
    {
        $rate = $this->toFloat($this->taxRate);

        if ($rate <= 0) {
            return 0.0;
        }

        return round($this->subtotalAmount() * $rate / 100, 2);
    }

    public function discountAmountCalculated(): float
    {
        $discountAmount = $this->toFloat($this->discountAmount);

        if ($discountAmount <= 0) {
            return 0.0;
        }

        if ($this->discountType === 'percent') {
            return round($this->subtotalAmount() * $discountAmount / 100, 2);
        }

        return $discountAmount;
    }

    public function totalAmount(): float
    {
        $subtotal = $this->subtotalAmount();
        $discount = $this->discountAmountCalculated();
        $tax = $this->taxAmount();

        return max(
            0.0,
            round(
                $subtotal - $discount + $tax,
                2,
            ),
        );
    }

    public function changeAmount(): float
    {
        return max(0.0, $this->toFloat($this->tendered) - $this->totalAmount());
    }


public function toDisplayDiscount(): float
    {
        return min(
            $this->discountAmountCalculated(),
            $this->subtotalAmount(),
        );
    }

    public function render(): View
    {
        $search = trim($this->search);

        $products = Product::query()
            ->where('status', 'active')
            ->when(
                $this->categoryId > 0,
                fn ($query) => $query->where('category_id', $this->categoryId),
            )
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($productQuery) use ($search): void {
                        $productQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('id', $search);
                    });
                },
            )
            ->orderBy('name')
            ->limit(48)
            ->get([
                'id',
                'name',
                'sku',
                'selling_price',
                'quantity',
                'reorder_level',
            ]);

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $selectedCustomer = $this->customerId > 0
            ? Customer::query()->find($this->customerId)
            : null;

        $customerResults = $this->customerSearch === ''
            ? new Collection
            : $this->searchCustomers($this->customerSearch);

        $cartProducts = Product::query()
            ->whereIn('id', array_keys($this->cart))
            ->get([
                'id',
                'name',
                'sku',
                'selling_price',
                'quantity',
                'reorder_level',
            ])
            ->keyBy('id');

        $cartLines = collect($this->cart)
            ->map(function (array $line, int $productId) use ($cartProducts): array {
                $product = $cartProducts->get($productId);

                $quantity = $this->toFloat($line['quantity']);
                $unitPrice = $this->toFloat($line['unit_price']);

                $available = $product !== null
                    ? (float) $product->quantity
                    : 0.0;

                return [
                    'product_id' => $productId,
                    'name' => $product->name ?? "Product #{$productId}",
                    'sku' => $product->sku ?? '',
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => number_format(
                        round($quantity * $unitPrice, 2),
                        2,
                        '.',
                        '',
                    ),
                    'stock' => $product->quantity ?? '0.000',
                    'stock_state' => $this->stockState(
                        $available,
                        $product !== null ? (float) $product->reorder_level : 0.0,
                    ),
                    'has_stock' => $product !== null
                        && $available >= $quantity,
                ];
            })
            ->values()
            ->all();

        return view(
            'livewire.admin.pos.index',
            [
                'products' => $products,
                'categories' => $categories,
                'selectedCustomer' => $selectedCustomer,
                'customerResults' => $customerResults,
                'cartLines' => $cartLines,
            ],
        )->layout(
            'layouts.app',
            [
                'title' => 'Point of Sale',
            ],
        );
    }


private function searchCustomers(string $search): Collection
    {
        return Customer::query()
            ->where('status', 'active')
            ->where(function ($query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(8)
            ->get([
                'id',
                'name',
                'phone',
                'email',
            ]);
    }


private function stockState(float $available, float $reorderLevel): string
    {
        if ($available <= 0) {
            return 'out';
        }

        if ($available <= $reorderLevel) {
            return 'low';
        }

        return 'in';
    }


private function toast(string $icon, string $title, string $text = ''): void
    {
        $this->dispatch('stockpilot-toast', icon: $icon, title: $title, text: $text);
    }


private function ensureWalkInCustomer(): Customer
    {
        return Customer::query()->firstOrCreate(
            ['name' => (string) config('stockpilot.walk_in_customer_name')],
            [
                'phone' => '0',
                'email' => null,
                'address' => null,
                'status' => 'active',
            ],
        );
    }

    private function toFloat(mixed $value): float
    {
        $number = (float) $value;

        return is_finite($number) ? $number : 0.0;
    }
}
