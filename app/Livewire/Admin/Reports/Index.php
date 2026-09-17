<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    #[Url(history: true)]
    public string $section = 'sales';

    #[Url(history: true)]
    public bool $print = false;

    #[Url(as: 'from', history: true)]
    public string $from = '';

    #[Url(as: 'to', history: true)]
    public string $to = '';

    #[Url(history: true)]
    public string $groupBy = 'day';

    #[Url(as: 'cust', history: true)]
    public string $customer = '';

    #[Url(as: 'sup', history: true)]
    public string $supplier = '';

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user->isActive()) {
            return;
        }

        $allowed = $this->allowedSections();

        if (! in_array($this->section, $allowed, true)) {
            $this->section = $allowed[0] ?? 'sales';
        }

        $this->from = $this->normalizeDateInput(
            $this->from,
            today()->startOfMonth()->toDateString(),
        );

        $this->to = $this->normalizeDateInput(
            $this->to,
            today()->toDateString(),
        );

        if (! in_array($this->groupBy, $this->allowedGroupings(), true)) {
            $this->groupBy = 'day';
        }

        $this->customer = $this->normalizeId($this->customer);
        $this->supplier = $this->normalizeId($this->supplier);
    }

    public function updatedCustomer(): void
    {
        $this->customer = $this->normalizeId($this->customer);
    }

    public function updatedSupplier(): void
    {
        $this->supplier = $this->normalizeId($this->supplier);
    }


public function allowedGroupings(): array
    {
        return ['day', 'week', 'month'];
    }


private function normalizeId(string $value): string
    {
        $value = trim($value);

        if ($value === '' || ! ctype_digit($value)) {
            return '';
        }

        return $value;
    }


private function normalizeDateInput(string $value, string $fallback): string
    {
        $value = trim($value);

        if ($value === '') {
            return $fallback;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $fallback;
        }

        if (! $date instanceof Carbon || $date->format('Y-m-d') !== $value) {
            return $fallback;
        }

        return $date->format('Y-m-d');
    }


private function dateRange(): array
    {
        $from = Carbon::parse(
            $this->normalizeDateInput(
                $this->from,
                today()->startOfMonth()->toDateString(),
            ),
        )->startOfDay();

        $to = Carbon::parse(
            $this->normalizeDateInput(
                $this->to,
                today()->toDateString(),
            ),
        )->endOfDay();

        if ($from->isAfter($to)) {
            return [$to->startOfDay(), $from->endOfDay()];
        }

        return [$from, $to];
    }


public function allowedSections(): array
    {
        $user = auth()->user();

        $sections = [];

        if ($user->isAdmin() || $user->isSalesUser()) {
            $sections[] = 'sales';
            $sections[] = 'outstanding';
            $sections[] = 'vat';
        }

        if ($user->isAdmin() || $user->isStockUser()) {
            $sections[] = 'purchases';
            $sections[] = 'valuation';
        }

        if ($user->isAdmin()) {
            $sections[] = 'profit';
        }

        return $sections;
    }


private function inRange(Builder $query, string $column): void
    {
        [$from, $to] = $this->dateRange();

        $query->whereBetween($column, [$from, $to]);
    }


public function salesReport(): array
    {
        if (! (auth()->user()->isAdmin() || auth()->user()->isSalesUser())) {
            abort(403);
        }

        $base = Invoice::query()
            ->where('status', 'completed');

        $this->inRange($base, 'invoice_date');

        $base = $this->applyCustomerFilter($base);

        $revenue = (float) $base->clone()->sum('total_amount');
        $invoiceCount = (int) $base->clone()->count();
        $taxCollected = (float) $base->clone()->sum('tax_amount');
        $itemCount = (float) InvoiceItem::query()
            ->whereHas(
                'invoice',
                function (Builder $query): void {
                    $query
                        ->where('status', 'completed');
                    $this->inRange($query, 'invoice_date');
                    $this->applyCustomerFilter($query);
                },
            )
            ->sum('quantity');

        $rows = Invoice::query()
            ->where('status', 'completed')
            ->selectRaw(
                'DATE(invoice_date) AS period, COUNT(*) AS invoices, SUM(total_amount) AS revenue, SUM(tax_amount) AS tax'
            )
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'invoice_date');
            })
            ->when(
                $this->groupBy === 'week',
                fn (Builder $query) => $query->selectRaw(
                    'YEARWEEK(invoice_date) AS sort'
                ),
            )
            ->when(
                $this->groupBy === 'month',
                fn (Builder $query) => $query->selectRaw(
                    'DATE_FORMAT(invoice_date, "%Y-%m") AS periodSort'
                ),
            )
            ->groupBy('period')
            ->orderBy('period')
            ->tap(fn (Builder $query) => $this->applyCustomerFilter($query))
            ->get()
            ->map(fn (Invoice $row): array => $row->toArray())
            ->all();

        return [
            'kpis' => [
                'revenue' => $revenue,
                'invoices' => $invoiceCount,
                'tax' => $taxCollected,
                'items' => $itemCount,
            ],
            'rows' => $rows,
        ];
    }


public function salesDetail(): array
    {
        if (! (auth()->user()->isAdmin() || auth()->user()->isSalesUser())) {
            abort(403);
        }

        $invoices = Invoice::query()
            ->where('invoices.status', 'completed')
            ->with('customer:id,name')
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
            ->select('invoices.*')
            ->selectRaw('COALESCE(payment_totals.paid_amount, 0) AS paid_amount')
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'invoices.invoice_date');
            })
            ->when(
                $this->customer !== '',
                fn (Builder $query): Builder => $query->where(
                    'invoices.customer_id',
                    $this->customer,
                ),
            )
            ->orderByDesc('invoices.invoice_date')
            ->orderByDesc('invoices.id')
            ->limit(300)
            ->get();

        return $invoices
            ->map(function (Invoice $invoice): array {
                $totalAmount = (float) $invoice->total_amount;
                $paidAmount = (float) $invoice->getAttribute('paid_amount');
                $customer = $invoice->customer;

                return [
                    'invoice_number' => (string) $invoice->invoice_number,
                    'invoice_date' => Carbon::parse($invoice->invoice_date)->toDateString(),
                    'customer' => (string) ($customer === null ? '' : (string) $customer->name),
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'balance' => max(0.0, round($totalAmount - $paidAmount, 2)),
                ];
            })
            ->all();
    }


private function applyCustomerFilter(Builder $query): Builder
    {
        if ($this->customer === '') {
            return $query;
        }

        return $query->where('invoices.customer_id', $this->customer);
    }


public function purchasesReport(): array
    {
        if (! (auth()->user()->isAdmin() || auth()->user()->isStockUser())) {
            abort(403);
        }

        $base = Purchase::query()
            ->where('status', 'completed');

        $this->inRange($base, 'purchase_date');

        $base = $this->applySupplierFilter($base);

        $spent = (float) $base->clone()->sum('total_amount');
        $purchaseCount = (int) $base->clone()->count();
        $taxPaid = (float) $base->clone()->sum('tax_amount');
        $itemCount = (float) PurchaseItem::query()
            ->whereHas(
                'purchase',
                function (Builder $query): void {
                    $query->where('status', 'completed');
                    $this->inRange($query, 'purchase_date');
                    $this->applySupplierFilter($query);
                },
            )
            ->sum('quantity');

        $rows = Purchase::query()
            ->where('status', 'completed')
            ->with('supplier:id,name,company')
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'purchase_date');
            })
            ->when(
                $this->supplier !== '',
                fn (Builder $query): Builder => $query->where(
                    'supplier_id',
                    $this->supplier,
                ),
            )
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(300)
            ->get()
            ->map(function (Purchase $purchase): array {
                $supplier = $purchase->supplier;

                return [
                    'purchase_number' => (string) $purchase->purchase_number,
                    'supplier' => (string) ($supplier === null ? '' : (string) $supplier->name),
                    'purchase_date' => Carbon::parse($purchase->purchase_date)->toDateString(),
                    'total_amount' => (float) $purchase->total_amount,
                ];
            })
            ->all();

        return [
            'kpis' => [
                'spent' => $spent,
                'purchases' => $purchaseCount,
                'tax' => $taxPaid,
                'items' => $itemCount,
            ],
            'rows' => $rows,
        ];
    }


private function applySupplierFilter(Builder $query): Builder
    {
        if ($this->supplier === '') {
            return $query;
        }

        return $query->where('supplier_id', $this->supplier);
    }


public function profitReport(): array
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $revenue = (float) Invoice::query()
            ->where('status', 'completed')
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'invoice_date');
            })
            ->sum('total_amount');

        $cogs = (float) InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('products', 'products.id', '=', 'invoice_items.product_id')
            ->where('invoices.status', 'completed')
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'invoices.invoice_date');
            })
            ->selectRaw(
                'SUM(invoice_items.quantity * products.cost_price) AS cogs'
            )
            ->value('cogs');

        $grossProfit = $revenue - $cogs;
        $margin = $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0.0;

        $rows = Invoice::query()
            ->where('invoices.status', 'completed')
            ->leftJoin(
                'invoice_items',
                'invoice_items.invoice_id',
                '=',
                'invoices.id',
            )
            ->leftJoin(
                'products',
                'products.id',
                '=',
                'invoice_items.product_id',
            )
            ->selectRaw(
                'DATE(invoices.invoice_date) AS period, SUM(invoices.total_amount) AS revenue, SUM(invoice_items.quantity * products.cost_price) AS cogs'
            )
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'invoices.invoice_date');
            })
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function (Invoice $row): array {
                $attributes = $row->toArray();

                $revenue = (float) ($attributes['revenue'] ?? 0);
                $cogs = (float) ($attributes['cogs'] ?? 0);
                $profit = $revenue - $cogs;

                return [
                    'period' => (string) ($attributes['period'] ?? ''),
                    'revenue' => $revenue,
                    'cogs' => $cogs,
                    'profit' => $profit,
                    'margin' => $revenue > 0
                        ? ($profit / $revenue) * 100
                        : 0.0,
                ];
            })
            ->all();

        return [
            'kpis' => [
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $grossProfit,
                'margin' => $margin,
            ],
            'rows' => $rows,
        ];
    }


public function valuationReport(): array
    {
        if (! (auth()->user()->isAdmin() || auth()->user()->isStockUser())) {
            abort(403);
        }

        $base = Product::query()->where('status', 'active');

        $costValue = (float) $base->clone()
            ->selectRaw('COALESCE(SUM(quantity * cost_price), 0) AS value')
            ->value('value');
        $sellValue = (float) $base->clone()
            ->selectRaw('COALESCE(SUM(quantity * selling_price), 0) AS value')
            ->value('value');
        $units = (float) $base->clone()->sum('quantity');
        $lowStock = (int) $base->clone()
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();
        $outOfStock = (int) $base->clone()
            ->where('quantity', '<=', 0)
            ->count();

        $rows = Product::query()
            ->where('status', 'active')
            ->with('category:id,name')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'category_id',
                'quantity',
                'cost_price',
                'selling_price',
                'reorder_level',
            ])
            ->map(function (Product $product): array {
                $category = $product->category;

                $quantity = (float) $product->quantity;
                $reorderLevel = (float) $product->reorder_level;

                $status = match (true) {
                    $quantity <= 0 => 'Out of Stock',
                    $quantity <= $reorderLevel => 'Low Stock',
                    default => 'In Stock',
                };

                return [
                    'name' => (string) $product->name,
                    'sku' => (string) $product->sku,
                    'category' => (string) ($category === null ? '' : $category->name),
                    'quantity' => $quantity,
                    'cost_price' => (float) $product->cost_price,
                    'selling_price' => (float) $product->selling_price,
                    'reorder_level' => $reorderLevel,
                    'status' => $status,
                ];
            })
            ->all();

        return [
            'kpis' => [
                'costValue' => $costValue,
                'sellValue' => $sellValue,
                'units' => $units,
                'lowStock' => $lowStock,
                'outOfStock' => $outOfStock,
            ],
            'rows' => $rows,
        ];
    }


public function outstandingReport(): array
    {
        if (! (auth()->user()->isAdmin() || auth()->user()->isSalesUser())) {
            abort(403);
        }

        [$from, $to] = $this->dateRange();

        $sales = Invoice::query()
            ->where('status', 'completed')
            ->whereBetween('invoice_date', [$from, $to])
            ->select('customer_id')
            ->selectRaw('SUM(total_amount) AS total_sales')
            ->groupBy('customer_id');

        $paid = Payment::query()
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->where('invoices.status', 'completed')
            ->whereBetween('invoices.invoice_date', [$from, $to])
            ->select('invoices.customer_id')
            ->selectRaw('SUM(payments.amount) AS total_paid')
            ->groupBy('invoices.customer_id');

        $base = Customer::query()
            ->leftJoinSub(
                $sales,
                'sales_totals',
                'sales_totals.customer_id',
                '=',
                'customers.id',
            )
            ->leftJoinSub(
                $paid,
                'paid_totals',
                'paid_totals.customer_id',
                '=',
                'customers.id',
            )
            ->whereNotNull('sales_totals.customer_id')
            ->whereRaw(
                'COALESCE(sales_totals.total_sales, 0) - COALESCE(paid_totals.total_paid, 0) > 0'
            );

        $totals = $base->clone()->selectRaw(
            'COALESCE(SUM(sales_totals.total_sales), 0) AS total_sales, '
            .'COALESCE(SUM(paid_totals.total_paid), 0) AS total_paid, '
            .'COALESCE(SUM(sales_totals.total_sales - COALESCE(paid_totals.total_paid, 0)), 0) AS outstanding'
        )->first();

        $totalSales = (float) ($totals->total_sales ?? 0);
        $totalPaid = (float) ($totals->total_paid ?? 0);
        $totalOutstanding = (float) ($totals->outstanding ?? 0);
        $customersWithBalance = (int) $base->clone()->count();

        $rows = $base->clone()
            ->select('customers.id', 'customers.name')
            ->selectRaw('COALESCE(sales_totals.total_sales, 0) AS total_sales')
            ->selectRaw('COALESCE(paid_totals.total_paid, 0) AS total_paid')
            ->selectRaw(
                'COALESCE(sales_totals.total_sales, 0) - COALESCE(paid_totals.total_paid, 0) AS outstanding'
            )
            ->orderByDesc('outstanding')
            ->orderBy('customers.name')
            ->get()
            ->map(fn (Customer $customer): array => [
                'customer' => (string) $customer->name,
                'total_sales' => (float) $customer->getAttribute('total_sales'),
                'total_paid' => (float) $customer->getAttribute('total_paid'),
                'outstanding' => (float) $customer->getAttribute('outstanding'),
            ])
            ->all();

        return [
            'kpis' => [
                'outstanding' => $totalOutstanding,
                'customers' => $customersWithBalance,
                'total_sales' => $totalSales,
                'total_paid' => $totalPaid,
            ],
            'rows' => $rows,
        ];
    }


public function vatReport(): array
    {
        if (! (auth()->user()->isAdmin() || auth()->user()->isSalesUser())) {
            abort(403);
        }

        $output = (float) Invoice::query()
            ->where('status', 'completed')
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'invoice_date');
            })
            ->sum('tax_amount');

        $input = (float) Purchase::query()
            ->where('status', 'completed')
            ->tap(function (Builder $query): void {
                $this->inRange($query, 'purchase_date');
            })
            ->sum('tax_amount');

        $net = $output - $input;

        return [
            'kpis' => [
                'output' => $output,
                'input' => $input,
                'net' => $net,
            ],
        ];
    }


public function exportCsv(): StreamedResponse
    {
        $allowed = $this->allowedSections();

        if (! in_array($this->section, $allowed, true)) {
            abort(403);
        }

        $from = $this->normalizeDateInput(
            $this->from,
            today()->startOfMonth()->toDateString(),
        );
        $to = $this->normalizeDateInput($this->to, today()->toDateString());

        $filename = sprintf(
            '%s-report-%s-to-%s.csv',
            $this->section,
            $from,
            $to,
        );

        $payload = $this->rowsForSection($this->section);

        return response()->streamDownload(function () use ($payload): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, $payload['headers']);

            foreach ($payload['rows'] as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }


protected function sectionRows(string $section): array
    {
        return match ($section) {
            'purchases' => $this->purchasesReport()['rows'],
            'profit' => $this->profitReport()['rows'],
            'valuation' => $this->valuationReport()['rows'],
            'outstanding' => $this->outstandingReport()['rows'],
            'vat' => [],
            default => $this->salesDetail(),
        };
    }


protected function sectionColumns(string $section): array
    {
        return match ($section) {
            'purchases' => [
                'purchase_number',
                'supplier',
                'purchase_date',
                'total_amount',
            ],
            'profit' => ['period', 'revenue', 'cogs', 'profit', 'margin'],
            'valuation' => [
                'name',
                'sku',
                'category',
                'quantity',
                'cost_price',
                'selling_price',
                'reorder_level',
                'status',
            ],
            'outstanding' => ['customer', 'total_sales', 'total_paid', 'outstanding'],
            'vat' => [],
            default => [
                'invoice_number',
                'invoice_date',
                'customer',
                'total_amount',
                'paid_amount',
                'balance',
            ],
        };
    }


protected function textColumns(): array
    {
        return [
            'period',
            'name',
            'sku',
            'category',
            'customer',
            'supplier',
            'purchase_number',
            'purchase_date',
            'invoice_number',
            'invoice_date',
            'status',
        ];
    }


protected function rowsForSection(string $section): array
    {
        $columns = $this->sectionColumns($section);
        $data = $this->sectionRows($section);

        $headers = array_map(
            fn (string $column) => __(ucwords(str_replace('_', ' ', $column))),
            $columns,
        );

        $textColumns = $this->textColumns();

        $rows = [];

        foreach ($data as $row) {
            $values = [];

            foreach ($columns as $column) {
                $value = $row[$column] ?? '';

                if (in_array($column, $textColumns, true)) {
                    $values[] = (string) $value;
                } else {
                    $values[] = number_format((float) $value, 2);
                }
            }

            $rows[] = $values;
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function render(): View
    {
        $this->from = $this->normalizeDateInput(
            $this->from,
            today()->startOfMonth()->toDateString(),
        );

        $this->to = $this->normalizeDateInput(
            $this->to,
            today()->toDateString(),
        );

        if (! in_array($this->groupBy, $this->allowedGroupings(), true)) {
            $this->groupBy = 'day';
        }

        $this->customer = $this->normalizeId($this->customer);
        $this->supplier = $this->normalizeId($this->supplier);

        $section = $this->section;
        $data = match ($section) {
            'purchases' => $this->purchasesReport(),
            'profit' => $this->profitReport(),
            'valuation' => $this->valuationReport(),
            'outstanding' => $this->outstandingReport(),
            'vat' => $this->vatReport(),
            default => $this->salesReport(),
        };

        $salesDetail = in_array($section, ['sales'], true)
            ? $this->salesDetail()
            : [];

        $allowed = $this->allowedSections();

        $customers = in_array($section, ['sales'], true)
            ? Customer::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
            : [];

        $suppliers = in_array($section, ['purchases'], true)
            ? Supplier::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'company'])
            : [];

        $viewData = [
            'section' => $section,
            'data' => $data,
            'salesDetail' => $salesDetail,
            'allowed' => $allowed,
            'customers' => $customers,
            'suppliers' => $suppliers,
            'reportTitle' => $this->reportTitle($section),
        ];

        if ($this->print) {
            return view('livewire.admin.reports.print', $viewData)
                ->layout('layouts.print', [
                    'title' => $this->reportTitle($section),
                ]);
        }

        return view(
            'livewire.admin.reports.index',
            $viewData,
        )->layout('layouts.app', [
            'title' => __('Reports'),
        ]);
    }


public function reportTitle(string $section): string
    {
        return match ($section) {
            'purchases' => __('Purchasing report'),
            'profit' => __('Profit report'),
            'valuation' => __('Stock valuation'),
            'outstanding' => __('Customer outstanding report'),
            'vat' => __('VAT report'),
            default => __('Sales report'),
        };
    }
}
