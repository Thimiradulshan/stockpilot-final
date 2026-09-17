<?php

namespace App\Livewire\Admin;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class Dashboard extends Component
{
    public function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };
    }

    public function render(): View
    {
        $user = auth()->user();

        $canSeeSales = $user !== null
            && ($user->isAdmin() || $user->isSalesUser());

        $canSeeStock = $user !== null
            && ($user->isAdmin() || $user->isStockUser());

        $today = today();

        $totalCustomers = $canSeeSales
            ? (int) Customer::query()->count()
            : 0;

        $totalProducts = $canSeeStock
            ? (int) Product::query()->where('status', 'active')->count()
            : 0;

        $totalSuppliers = $canSeeStock
            ? (int) Supplier::query()->count()
            : 0;

        $totalSales = $canSeeSales
            ? (float) Invoice::query()
                ->where('status', 'completed')
                ->sum('total_amount')
            : 0.0;

        $todaySalesTotal = $canSeeSales
            ? (float) Invoice::query()
                ->where('status', 'completed')
                ->whereDate('invoice_date', $today)
                ->sum('total_amount')
            : 0.0;

        $todayInvoiceCount = $canSeeSales
            ? Invoice::query()
                ->where('status', 'completed')
                ->whereDate('invoice_date', $today)
                ->count()
            : 0;

        $customersServedToday = $canSeeSales
            ? (int) Invoice::query()
                ->where('status', 'completed')
                ->whereDate('invoice_date', $today)
                ->distinct()
                ->count('customer_id')
            : 0;

        $salesTrend = [];
        $trendMaxValue = 1.0;

        if ($canSeeSales) {
            $totalsByDay = Invoice::query()
                ->where('status', 'completed')
                ->whereDate('invoice_date', '>=', $today->copy()->subDays(6))
                ->selectRaw('DATE(invoice_date) AS day')
                ->selectRaw('COALESCE(SUM(total_amount), 0) AS total')
                ->groupBy('day')
                ->pluck('total', 'day');

            for ($offset = 6; $offset >= 0; $offset--) {
                $day = $today->copy()->subDays($offset);

                $salesTrend[] = [
                    'label' => $day->format('D'),
                    'date' => $day->format('j M'),
                    'value' => (float) ($totalsByDay[$day->toDateString()] ?? 0),
                ];
            }

            $trendMaxValue = max(
                1.0,
                ...array_map(
                    static fn (array $bucket): float => $bucket['value'],
                    $salesTrend,
                ),
            );
        }

        $outstandingBalance = $canSeeSales
            ? (float) Invoice::query()
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
                ->value('outstanding_balance')
            : 0.0;

        $stockValue = $canSeeStock
            ? (float) Product::query()
                ->where('status', 'active')
                ->selectRaw(
                    'COALESCE(SUM(quantity * cost_price), 0) AS value'
                )
                ->value('value')
            : 0.0;

        $lowStockCount = $canSeeStock
            ? Product::query()
                ->where('status', 'active')
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->where('quantity', '>', 0)
                ->count()
            : 0;

        $outOfStockCount = $canSeeStock
            ? Product::query()
                ->where('status', 'active')
                ->where('quantity', '<=', 0)
                ->count()
            : 0;

        $recentInvoices = $canSeeSales
            ? Invoice::query()
                ->with([
                    'customer:id,name,phone',
                    'payments:id,invoice_id,amount',
                ])
                ->orderByDesc('id')
                ->limit(6)
                ->get()
            : new Collection;

        $recentPayments = $canSeeSales
            ? Payment::query()
                ->with([
                    'invoice:id,invoice_number,total_amount,customer_id',
                    'invoice.customer:id,name',
                    'receivedBy:id,name',
                ])
                ->orderByDesc('payment_date')
                ->orderByDesc('id')
                ->limit(6)
                ->get()
            : new Collection;

        $lowStockProducts = $canSeeStock
            ? Product::query()
                ->with('category:id,name')
                ->where('status', 'active')
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->where('quantity', '>', 0)
                ->orderBy('quantity')
                ->limit(8)
                ->get([
                    'id',
                    'name',
                    'sku',
                    'quantity',
                    'reorder_level',
                    'category_id',
                ])
            : new Collection;

        return view('livewire.admin.dashboard', [
            'canSeeSales' => $canSeeSales,
            'canSeeStock' => $canSeeStock,
            'greeting' => $this->greeting(),
            'todayLabel' => $today->format('l, j F Y'),
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
            'totalSuppliers' => $totalSuppliers,
            'totalSales' => $totalSales,
            'todaySalesTotal' => $todaySalesTotal,
            'todayInvoiceCount' => $todayInvoiceCount,
            'customersServedToday' => $customersServedToday,
            'salesTrend' => $salesTrend,
            'trendMaxValue' => $trendMaxValue,
            'outstandingBalance' => $outstandingBalance,
            'stockValue' => $stockValue,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'lowStockProducts' => $lowStockProducts,
        ])->layout('layouts.app', [
            'title' => 'Dashboard',
        ]);
    }
}
