<x-stockpilot.admin-page
    x-data="productManager({ createProductOpen: @js($errors->any()) })"
    escapeAction="closeProducts()"
>

    <x-stockpilot.page-header
        :section-label="__('Workspace')"
        :section-current="__('Products')"
        :title="__('Products')"
        :subtitle="__('Product catalog')"
        :description="__('Manage your product catalog, pricing, stock levels, and availability.')"
        :tags="[
            ['label' => __('Catalog'), 'tone' => 'primary'],
            ['label' => __('Inventory'), 'tone' => 'info'],
        ]"
    >
        <x-slot:actions>
            @can('create', \App\Models\Product::class)
                <x-stockpilot.primary-action
                    :label="__('Add product')"
                    icon="products"
                    click="openCreateProduct()"
                />
            @endcan
        </x-slot:actions>
    </x-stockpilot.page-header>

    <x-stockpilot.master-data-tabs />

    <x-stockpilot.toast-alerts />

    <section aria-label="{{ __('Product summary') }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-stockpilot.kpi-card
                :label="__('Total products')"
                :value="number_format($totalProducts)"
                :hint="__('Entire catalog')"
                icon="products"
                tone="primary"
                action="$set('status', ''); $set('stockLevel', '')"
                :active="$status === '' && $stockLevel === ''"
            />

            <x-stockpilot.kpi-card
                :label="__('Active products')"
                :value="number_format($activeProducts)"
                :hint="__('Available for operations')"
                icon="check"
                tone="success"
                action="$set('status', 'active'); $set('stockLevel', '')"
                :active="$status === 'active' && $stockLevel === ''"
            />

            <x-stockpilot.kpi-card
                :label="__('Low stock')"
                :value="number_format($lowStockProducts)"
                :hint="__('Needs attention')"
                icon="alert-triangle"
                tone="warning"
                action="$set('status', 'active'); $set('stockLevel', 'low')"
                :active="$stockLevel === 'low'"
            />

            <x-stockpilot.kpi-card
                :label="__('Out of stock')"
                :value="number_format($outOfStockProducts)"
                :hint="__('Immediate attention')"
                icon="x-circle"
                tone="danger"
                action="$set('status', 'active'); $set('stockLevel', 'out')"
                :active="$stockLevel === 'out'"
            />

        </div>
    </section>

    <div class="space-y-3">

        <div
            wire:loading
            wire:target="search,category,status"
            class="inline-flex items-center gap-2 rounded-full bg-sp-info/10 px-3 py-1.5 text-xs font-bold text-sp-info-foreground dark:bg-sp-info-soft dark:text-sp-info"
        >
            <svg class="h-3.5 w-3.5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z"></path>
            </svg>

            {{ __('Updating results') }}
        </div>

    </div>

    <x-stockpilot.filter-bar aria-label="{{ __('Product filters') }}">

        <div class="w-full xl:max-w-2xl">
            <x-stockpilot.search-input
                id="product-search"
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search by product name or SKU...')"
            />
        </div>

        <div class="flex flex-wrap items-center gap-2">

            <select
                wire:model.live="category"
                class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text shadow-sm outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
                <option value="">{{ __('All categories') }}</option>

                @foreach ($categories as $categoryOption)
                    <option value="{{ $categoryOption->id }}">
                        {{ $categoryOption->name }}
                    </option>
                @endforeach
            </select>

            <select
                wire:model.live="status"
                class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text shadow-sm outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
                <option value="active">{{ __('Active') }}</option>
                <option value="inactive">{{ __('Inactive') }}</option>
                <option value="">{{ __('All statuses') }}</option>
            </select>

            <select
                wire:model.live="stockLevel"
                class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text shadow-sm outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
                <option value="">{{ __('All stock levels') }}</option>
                <option value="low">{{ __('Low stock') }}</option>
                <option value="out">{{ __('Out of stock') }}</option>
            </select>

            @if ($this->hasActiveFilters())
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="rounded-xl border border-sp-info/30 bg-sp-info-soft px-4 py-3.5 text-sm font-extrabold text-sp-info-foreground shadow-sm transition hover:bg-sp-info-soft dark:bg-sp-info-soft dark:text-sp-info"
                >
                    {{ __('Clear filters') }}
                </button>
            @endif

        </div>

    </x-stockpilot.filter-bar>

    <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Product catalog') }}">

        <table class="min-w-full border-collapse">

            <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">

                <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Product') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Category') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Selling price') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Current stock') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Reorder level') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Status') }}
                    </th>

                    <th scope="col" class="w-[240px] px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Actions') }}
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                @forelse ($products as $product)
                    @php
                        $isOutOfStock = $product->quantity <= 0;
                        $isLowStock = ! $isOutOfStock && $product->quantity <= $product->reorder_level;
                    @endphp

                    <tr
                        wire:key="product-row-{{ $product->id }}"
                        class="group transition duration-150 odd:bg-sp-surface even:bg-sp-surface-muted/40 hover:bg-sp-primary/[0.04] dark:odd:bg-sp-surface dark:even:bg-sp-surface-muted dark:hover:bg-sp-brand-dark/70"
                    >

                        <td class="px-6 py-5">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary shadow-sm dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

                                    <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-sp-accent/25"></span>

                                    <x-stockpilot.icon name="products" class="relative h-5 w-5" />

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-[15px] font-extrabold text-sp-text">
                                        {{ $product->name }}
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold tracking-wide text-sp-text-subtle">
                                        {{ __('SKU') }}: {{ $product->sku }}
                                    </p>

                                </div>

                            </div>

                        </td>

                        <td class="px-6 py-5">

                            <span class="text-sm font-semibold text-sp-text">
                                {{ $product->category?->name ?? '—' }}
                            </span>

                        </td>

                        <td class="whitespace-nowrap px-6 py-5 text-right">

                            <span class="text-sm font-extrabold text-sp-primary dark:text-sp-success">
                                Rs. {{ number_format((float) $product->selling_price, 2) }}
                            </span>

                        </td>

                        <td class="whitespace-nowrap px-6 py-5 text-right">

                            <div class="flex flex-col items-end gap-1.5">

                                <span class="text-sm font-extrabold text-sp-text">
                                    {{ number_format((float) $product->quantity, 3) }}
                                </span>

                                @if ($product->status !== 'active')
                                    <x-stockpilot.status-badge :label="__('Inactive')" tone="muted" />
                                @elseif ($isOutOfStock)
                                    <x-stockpilot.status-badge :label="__('Out of stock')" tone="danger" />
                                @elseif ($isLowStock)
                                    <x-stockpilot.status-badge :label="__('Low stock')" tone="warning" />
                                @else
                                    <x-stockpilot.status-badge :label="__('Normal')" tone="success" />
                                @endif

                            </div>

                        </td>

                        <td class="whitespace-nowrap px-6 py-5 text-right">

                            <span class="text-sm font-semibold text-sp-text-muted">
                                {{ number_format((float) $product->reorder_level, 3) }}
                            </span>

                        </td>

                        <td class="px-6 py-5 text-center">

                            @if ($product->status === 'active')
                                <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                            @else
                                <x-stockpilot.status-badge :label="__('Inactive')" tone="muted" />
                            @endif

                        </td>

                        <td class="px-6 py-5">

                            <div class="flex flex-wrap items-center gap-2">

                                @can('update', $product)
                                    <button
                                        type="button"
                                        @click="openEditProduct(@js([
                                            'id' => $product->id,
                                            'category_id' => $product->category_id,
                                            'name' => $product->name,
                                            'sku' => $product->sku,
                                            'cost_price' => (string) $product->cost_price,
                                            'selling_price' => (string) $product->selling_price,
                                            'reorder_level' => (string) $product->reorder_level,
                                            'description' => $product->description,
                                        ]))"
                                        class="inline-flex items-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm font-extrabold text-sp-text shadow-sm transition hover:border-sp-primary/30 hover:bg-sp-primary/10 hover:text-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text dark:hover:bg-sp-surface-muted dark:hover:text-sp-success"
                                    >
                                        <x-stockpilot.icon name="edit" class="h-4 w-4" />
                                        {{ __('Edit') }}
                                    </button>
                                @endcan

                                @can('adjustStock', $product)
                                    <button
                                        type="button"
                                        @click="openStockAdjust(@js([
                                            'id' => $product->id,
                                            'name' => $product->name,
                                            'sku' => $product->sku,
                                            'quantity' => number_format((float) $product->quantity, 3),
                                        ]))"
                                        class="inline-flex items-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm font-extrabold text-sp-text shadow-sm transition hover:border-sp-success/30 hover:bg-sp-success-soft hover:text-sp-success focus:outline-none focus:ring-2 focus:ring-sp-success/20 dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text dark:hover:bg-sp-surface-muted dark:hover:text-sp-success"
                                    >
                                        <x-stockpilot.icon name="shopping-cart" class="h-4 w-4" />
                                        {{ __('Stock') }}
                                    </button>
                                @endcan

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center">

                            <x-stockpilot.empty-state
                                icon="products"
                                :title="$this->hasActiveFilters() ? __('No matching products') : __('No products yet')"
                                :message="$this->hasActiveFilters() ? __('Try changing your search or filters to find the products you are looking for.') : __('Create your first product to start managing your inventory.')"
                            >
                                @if ($this->hasActiveFilters())
                                    <button
                                        type="button"
                                        wire:click="clearFilters"
                                        class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text transition hover:bg-sp-surface-muted"
                                    >
                                        {{ __('Clear filters') }}
                                    </button>
                                @endif

                                @can('create', \App\Models\Product::class)
                                    @if (! $this->hasActiveFilters())
                                        <button
                                            type="button"
                                            @click="openCreateProduct()"
                                            class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-md transition hover:bg-sp-primary-hover"
                                        >
                                            {{ __('Add product') }}
                                        </button>
                                    @endif
                                @endcan
                            </x-stockpilot.empty-state>

                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </x-stockpilot.page-table>

    <div class="space-y-3 lg:hidden">

        @forelse ($products as $product)
            @php
                $isOutOfStock = $product->quantity <= 0;
                $isLowStock = ! $isOutOfStock && $product->quantity <= $product->reorder_level;
            @endphp

            <article class="rounded-2xl border border-sp-border bg-sp-surface p-4 shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <div class="flex items-start justify-between gap-3">

                    <div class="flex min-w-0 items-center gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">
                            <x-stockpilot.icon name="products" class="h-5 w-5" />
                        </div>

                        <div class="min-w-0">

                            <h3 class="truncate text-sm font-bold text-sp-text">
                                {{ $product->name }}
                            </h3>

                            <p class="mt-0.5 truncate text-xs text-sp-text-subtle">
                                {{ __('SKU') }}: {{ $product->sku }}
                            </p>

                        </div>

                    </div>

                    @if ($product->status === 'active')
                        <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                    @else
                        <x-stockpilot.status-badge :label="__('Inactive')" tone="muted" />
                    @endif

                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">

                    <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Selling price') }}
                        </p>

                        <p class="mt-1 text-sm font-extrabold text-sp-primary dark:text-sp-success">
                            Rs. {{ number_format((float) $product->selling_price, 2) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Current stock') }}
                        </p>

                        <p class="mt-1 text-sm font-extrabold text-sp-text">
                            {{ number_format((float) $product->quantity, 3) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Reorder level') }}
                        </p>

                        <p class="mt-1 text-sm font-semibold text-sp-text">
                            {{ number_format((float) $product->reorder_level, 3) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Category') }}
                        </p>

                        <p class="mt-1 truncate text-sm font-semibold text-sp-text">
                            {{ $product->category?->name ?? '—' }}
                        </p>
                    </div>

                </div>

                <div class="mt-4">

                    @if ($product->status !== 'active')
                        <x-stockpilot.status-badge :label="__('Stock monitoring paused')" tone="muted" />
                    @elseif ($isOutOfStock)
                        <x-stockpilot.status-badge :label="__('Out of stock')" tone="danger" />
                    @elseif ($isLowStock)
                        <x-stockpilot.status-badge :label="__('Low stock')" tone="warning" />
                    @else
                        <x-stockpilot.status-badge :label="__('Stock normal')" tone="success" />
                    @endif

                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">

                    @can('update', $product)
                        <button
                            type="button"
                            @click="openEditProduct(@js([
                                'id' => $product->id,
                                'category_id' => $product->category_id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'cost_price' => (string) $product->cost_price,
                                'selling_price' => (string) $product->selling_price,
                                'reorder_level' => (string) $product->reorder_level,
                                'description' => $product->description,
                            ]))"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
                        >
                            <x-stockpilot.icon name="edit" class="h-4 w-4" />
                            {{ __('Edit') }}
                        </button>
                    @endcan

                    @can('adjustStock', $product)
                        <button
                            type="button"
                            @click="openStockAdjust(@js([
                                'id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'quantity' => number_format((float) $product->quantity, 3),
                            ]))"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
                        >
                            <x-stockpilot.icon name="shopping-cart" class="h-4 w-4" />
                            {{ __('Stock') }}
                        </button>
                    @endcan

                </div>

            </article>

        @empty

            <div class="rounded-2xl border border-sp-border bg-sp-surface px-6 py-14 text-center shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <x-stockpilot.empty-state
                    icon="products"
                    :title="__('No products found')"
                    :message="__('Try changing your search or filters.')"
                />

            </div>

        @endforelse

        @if ($products->hasPages())
            <div>
                {{ $products->links() }}
            </div>
        @endif

    </div>

    <x-stockpilot.pagination-bar
        :paginator="$products"
        :item-label="__('products')"
    />

    @can('create', \App\Models\Product::class)
        @include('livewire.admin.products.create-modal')
    @endcan

    @include('livewire.admin.products.edit-modal')

    @include('livewire.admin.products.stock-adjust-modal')

</x-stockpilot.admin-page>