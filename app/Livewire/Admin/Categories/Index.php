<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
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
        $this->authorize('viewAny', Category::class);
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
        $categories = Category::query()
            ->withCount('products')
            ->when(
                trim($this->search) !== '',
                function ($query): void {
                    $search = trim($this->search);

                    $query->where(function ($categoryQuery) use ($search): void {
                        $categoryQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere(
                                'description',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->when(
                $this->status !== '',
                fn ($query) => $query->where(
                    'status',
                    $this->status
                )
            )
            ->orderBy('name')
            ->paginate(10);

        $totalCategories = Category::query()->count();

        $activeCategories = Category::query()
            ->where('status', 'active')
            ->count();

        $inactiveCategories = Category::query()
            ->where('status', 'inactive')
            ->count();

        return view('livewire.admin.categories.index', [
            'categories' => $categories,
            'totalCategories' => $totalCategories,
            'activeCategories' => $activeCategories,
            'inactiveCategories' => $inactiveCategories,
        ])->layout('layouts.app', [
            'title' => 'Categories',
        ]);
    }
}
