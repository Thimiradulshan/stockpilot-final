<?php

namespace App\Livewire\Admin\Purchases;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
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

    public function mount(): void
    {
        $this->authorize('viewAny', Purchase::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';

        $this->resetPage();
    }

    public function hasActiveFilters(): bool
    {
        return trim($this->search) !== ''
            || $this->status !== '';
    }

    public function render(): View
    {
        $search = trim($this->search);

        $purchases = Purchase::query()
            ->with([
                'supplier:id,name,company',
                'createdBy:id,name',
                'items.product:id,name,sku',
            ])
            ->withCount('items')
            ->when(
                $search !== '',
                function ($query) use ($search): void {
                    $query->where(function ($purchaseQuery) use ($search): void {
                        $purchaseQuery
                            ->where(
                                'purchase_number',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'supplier',
                                function ($supplierQuery) use ($search): void {
                                    $supplierQuery
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'company',
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
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(10);

        $suppliers = Supplier::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'company',
            ]);

        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'cost_price',
                'quantity',
            ]);

        $totalPurchases = Purchase::query()->count();

        $completedPurchases = Purchase::query()
            ->where('status', 'completed')
            ->count();

        $cancelledPurchases = Purchase::query()
            ->where('status', 'cancelled')
            ->count();

        $completedPurchaseValue = Purchase::query()
            ->where('status', 'completed')
            ->sum('total_amount');

        return view(
            'livewire.admin.purchases.index',
            [
                'purchases' => $purchases,
                'suppliers' => $suppliers,
                'products' => $products,
                'totalPurchases' => $totalPurchases,
                'completedPurchases' => $completedPurchases,
                'cancelledPurchases' => $cancelledPurchases,
                'completedPurchaseValue' => $completedPurchaseValue,
            ],
        )->layout(
            'layouts.app',
            [
                'title' => 'Purchases',
            ],
        );
    }
}
