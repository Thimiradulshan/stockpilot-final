@php
    $candidates = [
        [
            'label' => __('Products'),
            'href' => route('admin.products.index'),
            'active' => request()->routeIs('admin.products.*'),
            'icon' => 'products',
            'model' => App\Models\Product::class,
        ],
        [
            'label' => __('Categories'),
            'href' => route('admin.categories.index'),
            'active' => request()->routeIs('admin.categories.*'),
            'icon' => 'categories',
            'model' => App\Models\Category::class,
        ],
        [
            'label' => __('Suppliers'),
            'href' => route('admin.suppliers.index'),
            'active' => request()->routeIs('admin.suppliers.*'),
            'icon' => 'suppliers',
            'model' => App\Models\Supplier::class,
        ],
        [
            'label' => __('Customers'),
            'href' => route('admin.customers.index'),
            'active' => request()->routeIs('admin.customers.*'),
            'icon' => 'customers',
            'model' => App\Models\Customer::class,
        ],
    ];

    $items = collect($candidates)
        ->filter(fn (array $item) => auth()->user()->can('viewAny', $item['model']))
        ->map(fn (array $item) => collect($item)->except('model')->all())
        ->values()
        ->all();
@endphp

@if (count($items) > 1)
    <x-stockpilot.tabs :items="$items" />
@endif