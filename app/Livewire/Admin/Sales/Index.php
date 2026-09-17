<?php

namespace App\Livewire\Admin\Sales;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $status = '';

    #[Url(as: 'payment', history: true)]
    public string $paymentStatus = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Invoice::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->paymentStatus = '';

        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return trim($this->search) !== ''
            || $this->status !== ''
            || $this->paymentStatus !== '';
    }

    public function render(): View
    {
        $search = trim($this->search);

        $invoices = Invoice::query()
            ->with([
                'customer:id,name,phone,email',
                'createdBy:id,name',
                'items.product:id,name,sku',
                'payments:id,invoice_id,amount,payment_date,payment_method',
            ])
            ->withCount('items')
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($invoiceQuery) use ($search): void {
                        $invoiceQuery
                            ->where(
                                'invoice_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'customer',
                                function ($customerQuery) use ($search): void {
                                    $customerQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'phone',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    });
                },
            )
            ->when(
                $this->status !== '',
                fn ($query) => $query->where(
                    'status',
                    $this->status
                ),
            )
            ->when(
                $this->paymentStatus !== '',
                fn ($query) => $query->where(
                    'payment_status',
                    $this->paymentStatus
                ),
            )
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(10);

        $customers = Customer::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'phone',
                'email',
            ]);

        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'selling_price',
                'quantity',
            ]);

        $totalInvoices = Invoice::query()->count();

        $voidedInvoices = Invoice::query()
            ->where('status', 'voided')
            ->count();

        $completedInvoiceValue = Invoice::query()
            ->where('status', 'completed')
            ->sum('total_amount');

        $outstandingBalance = Invoice::query()
            ->where('invoices.status', 'completed')
            ->whereIn('invoices.payment_status', [
                'unpaid',
                'partially_paid',
            ])
            ->leftJoinSub(
                Payment::query()
                    ->select('invoice_id')
                    ->selectRaw('SUM(amount) AS paid_amount')
                    ->groupBy('invoice_id'),
                'payment_totals',
                'payment_totals.invoice_id',
                '=',
                'invoices.id',
            )
            ->selectRaw(
                'COALESCE(SUM(invoices.total_amount - COALESCE(payment_totals.paid_amount, 0)), 0) AS outstanding_balance'
            )
            ->value('outstanding_balance');

        return view(
            'livewire.admin.sales.index',
            [
                'invoices' => $invoices,
                'customers' => $customers,
                'products' => $products,
                'totalInvoices' => $totalInvoices,
                'voidedInvoices' => $voidedInvoices,
                'completedInvoiceValue' => $completedInvoiceValue,
                'outstandingBalance' => $outstandingBalance,
            ],
        )->layout(
            'layouts.app',
            [
                'title' => 'Sales',
            ],
        );
    }
}
