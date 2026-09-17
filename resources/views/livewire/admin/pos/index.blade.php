<x-stockpilot.admin-page
    x-data="{}"
    @keydown.window.meta.k.prevent="$refs.posProductSearch?.focus()"
    @keydown.window.ctrl.k.prevent="$refs.posProductSearch?.focus()"
>

    
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sp-primary/10 text-sp-primary dark:bg-sp-brand-dark dark:text-sp-success">
                <x-stockpilot.icon name="shopping-cart" class="h-6 w-6" />
            </span>

            <div>
                <h1 class="text-xl font-black tracking-tight text-sp-text sm:text-2xl">
                    {{ __('Point of Sale') }}
                </h1>

                <p class="text-sm font-semibold text-sp-text-muted">
                    {{ __('Search, sell, collect payment and update stock in one screen.') }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                wire:click="resetSale"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-lg shadow-sp-primary/20 transition hover:-translate-y-0.5 hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15"
            >
                <x-stockpilot.icon name="plus" class="h-4 w-4" />
                {{ __('New sale') }}
            </button>

            <a
                href="{{ route('admin.sales.index') }}"
                wire:navigate
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text transition hover:border-sp-primary/40 hover:text-sp-primary focus:outline-none focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
            >
                <x-stockpilot.icon name="receipt" class="h-4 w-4" />
                {{ __('Sales history') }}
            </a>
        </div>

    </div>

    
    @if ($notice)
        <div
            @class([
                'flex items-start gap-3 rounded-2xl border px-4 py-3 text-sm font-semibold',
                'border-sp-success/30 bg-sp-success-soft text-sp-success' => str_contains($notice, ' added.'),
                'border-sp-warning/40 bg-sp-warning-soft text-sp-warning-foreground dark:text-sp-warning' => ! str_contains($notice, ' added.'),
            ])
            x-data
            x-init="setTimeout(() => $el.remove(), 6000)"
        >
            <x-stockpilot.icon name="info" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ $notice }}</span>
        </div>
    @endif

    @error('sale')
        <div
            class="flex items-start gap-3 rounded-2xl border border-sp-danger/30 bg-sp-danger-soft px-4 py-3 text-sm font-semibold text-sp-danger"
            x-data
            x-init="setTimeout(() => $el.remove(), 7000)"
        >
            <x-stockpilot.icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ $message }}</span>
        </div>
    @enderror

    @if ($successSale)
        
        <div class="mx-auto max-w-2xl">
            <div class="overflow-hidden rounded-3xl border border-sp-border-strong/60 bg-sp-surface shadow-sm">

                <div class="border-b border-sp-border-strong/60 bg-sp-success-soft/60 px-6 py-8 text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-sp-success text-white shadow-lg shadow-sp-success/30">
                        <x-stockpilot.icon name="check" class="h-9 w-9" />
                    </span>

                    <h2 class="mt-5 text-2xl font-extrabold tracking-tight text-sp-text">
                        {{ __('Sale completed') }}
                    </h2>

                    <p class="mt-1.5 text-sm text-sp-text-muted">
                        {{ __('Invoice') }}
                        <span class="font-bold text-sp-text">{{ $successSale['invoice_number'] }}</span>
                        {{ __('saved and stock updated.') }}
                    </p>
                </div>

                <div class="px-6 py-6">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Total') }}
                            </p>
                            <p class="mt-1 text-lg font-extrabold tabular-nums text-sp-text">
                                Rs {{ number_format((float) $successSale['total'], 2) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Paid') }}
                            </p>
                            <p class="mt-1 text-lg font-extrabold tabular-nums text-sp-text">
                                Rs {{ number_format((float) $successSale['paid'], 2) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Change') }}
                            </p>
                            <p class="mt-1 text-lg font-extrabold tabular-nums text-sp-success">
                                Rs {{ number_format((float) $successSale['change'], 2) }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Method') }}
                            </p>
                            <p class="mt-1 text-lg font-extrabold capitalize text-sp-text">
                                {{ __(str_replace('_', ' ', $successSale['payment_method'])) }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between rounded-2xl bg-sp-surface-muted/70 px-4 py-3 text-sm">
                        <span class="font-semibold text-sp-text-muted">
                            {{ __('Payment status') }}
                        </span>

                        <span
                            @class([
                                'rounded-full px-3 py-1 text-xs font-extrabold capitalize ring-1 ring-inset',
                                'bg-sp-success-soft text-sp-success ring-sp-success/20' => $successSale['payment_status'] === 'paid',
                                'bg-sp-warning-soft text-sp-warning-foreground ring-sp-warning/20 dark:text-sp-warning' => $successSale['payment_status'] !== 'paid',
                            ])
                        >
                            {{ __(str_replace('_', ' ', $successSale['payment_status'])) }}
                        </span>
                    </div>

                    <div class="mt-6 grid grid-cols-2 gap-3">
                        <a
                            href="{{ route('admin.sales.print', $successSale['invoice_id']) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3 text-sm font-bold text-sp-text transition hover:bg-sp-surface-muted"
                        >
                            <x-stockpilot.icon name="receipt" class="h-4 w-4" />
                            {{ __('Print receipt') }}
                        </a>

                        <button
                            type="button"
                            wire:click="resetSale"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-4 py-3 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15"
                        >
                            <x-stockpilot.icon name="plus" class="h-4 w-4" />
                            {{ __('Start new sale') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @else
        
        <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-[minmax(0,1fr)_420px] xl:grid-cols-[minmax(0,1fr)_460px]">

            
            <div class="space-y-5">

                
                <section class="rounded-2xl border border-sp-border-strong/60 bg-sp-surface p-5 shadow-sm">

                    <header class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-info-soft text-sp-info-foreground dark:text-sp-info">
                                <x-stockpilot.icon name="customers" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                                    {{ __('Customer') }}
                                </p>
                                <h2 class="truncate text-base font-extrabold text-sp-text">
                                    {{ __('Who is buying?') }}
                                </h2>
                            </div>
                        </div>

                        @if ($selectedCustomer)
                            <span @class([
                                'rounded-full px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.06em]',
                                'bg-sp-surface-muted text-sp-text-muted' => (int) $selectedCustomer->id === $walkInCustomerId,
                                'bg-sp-success-soft text-sp-success' => (int) $selectedCustomer->id !== $walkInCustomerId,
                            ])>
                                {{ (int) $selectedCustomer->id === $walkInCustomerId ? __('Walk-in') : __('Selected') }}
                            </span>
                        @endif
                    </header>

                    <div class="mt-4">
                        @if ($selectedCustomer && (int) $selectedCustomer->id !== $walkInCustomerId)
                            <div class="flex items-center gap-3 rounded-xl border border-sp-success/30 bg-sp-success-soft/50 p-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sp-success text-white">
                                    <x-stockpilot.icon name="customers" class="h-5 w-5" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-extrabold text-sp-text">{{ $selectedCustomer->name }}</p>
                                    <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                        {{ $selectedCustomer->phone ?: '—' }}
                                        @if ($selectedCustomer->email)
                                            · {{ $selectedCustomer->email }}
                                        @endif
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="clearCustomer"
                                    class="shrink-0 rounded-lg border border-sp-border-strong bg-sp-surface px-3 py-1.5 text-xs font-bold text-sp-text transition hover:border-sp-primary/40 hover:text-sp-primary"
                                >
                                    {{ __('Change customer') }}
                                </button>
                            </div>
                        @elseif ($selectedCustomer)
                            <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-surface-muted/50 p-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sp-surface text-sp-text-muted">
                                    <x-stockpilot.icon name="user" class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-extrabold text-sp-text">{{ $selectedCustomer->name }}</p>
                                    <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                        {{ __('Anonymous retail sale — no customer details required.') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3 rounded-xl border border-dashed border-sp-border-strong bg-sp-surface-muted/40 p-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sp-surface text-sp-text-subtle">
                                    <x-stockpilot.icon name="user" class="h-5 w-5" />
                                </div>
                                <p class="text-sm font-semibold text-sp-text-muted">
                                    {{ __('No customer selected.') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto]">

                        <div class="relative">
                            <label for="pos-customer-search" class="sr-only">{{ __('Search customer') }}</label>

                            <div class="relative">
                                <svg class="pointer-events-none absolute start-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-sp-text-subtle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <circle cx="11" cy="11" r="7" />
                                    <path d="m20 20-3.5-3.5" stroke-linecap="round" />
                                </svg>

                                <input
                                    id="pos-customer-search"
                                    type="search"
                                    wire:model.live.debounce.300ms="customerSearch"
                                    maxlength="150"
                                    placeholder="{{ __('Search customer by name, phone or email…') }}"
                                    autocomplete="off"
                                    @keydown.enter.prevent="$wire.selectCustomer({{ optional($customerResults->first())->id ?? 0 }})"
                                    class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface py-3 ps-11 pe-3.5 text-sm font-semibold text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface-soft"
                                />
                            </div>

                            @if ($customerSearch !== '')
                                <div class="absolute inset-x-0 top-full z-30 mt-1 max-h-72 overflow-y-auto rounded-xl border border-sp-border-strong bg-sp-surface p-1.5 shadow-xl">
                                    @forelse ($customerResults as $customer)
                                        <button
                                            type="button"
                                            wire:key="pos-customer-result-{{ $customer->id }}"
                                            wire:click="selectCustomer({{ $customer->id }})"
                                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-start transition hover:bg-sp-surface-muted"
                                        >
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sp-info-soft text-sp-info-foreground dark:text-sp-info">
                                                <x-stockpilot.icon name="customers" class="h-4 w-4" />
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate text-sm font-bold text-sp-text">{{ $customer->name }}</span>
                                                <span class="block truncate text-xs font-semibold text-sp-text-subtle">
                                                    {{ $customer->phone ?: '—' }}
                                                    @if ($customer->email)
                                                        · {{ $customer->email }}
                                                    @endif
                                                </span>
                                            </span>
                                        </button>
                                    @empty
                                        <div class="px-3 py-6 text-center">
                                            <p class="text-sm font-semibold text-sp-text-muted">{{ __('No customers found.') }}</p>
                                            <button
                                                type="button"
                                                wire:click="showQuickCustomer"
                                                class="mt-2 inline-flex items-center gap-1.5 text-xs font-bold text-sp-primary hover:text-sp-primary-hover dark:text-sp-success"
                                            >
                                                <x-stockpilot.icon name="plus" class="h-3.5 w-3.5" />
                                                {{ __('Add a new customer') }}
                                            </button>
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                wire:click="showQuickCustomer"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3 text-sm font-bold text-sp-text transition hover:border-sp-primary/40 hover:text-sp-primary lg:flex-none"
                            >
                                <x-stockpilot.icon name="plus" class="h-4 w-4" />
                                {{ __('Quick customer') }}
                            </button>

                            <button
                                type="button"
                                wire:click="useWalkInCustomer"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3 text-sm font-bold text-sp-text transition hover:border-sp-primary/40 hover:text-sp-primary lg:flex-none"
                            >
                                <x-stockpilot.icon name="user" class="h-4 w-4" />
                                {{ __('Walk-in') }}
                            </button>
                        </div>

                    </div>
                </section>

                
                <section class="rounded-2xl border border-sp-border-strong/60 bg-sp-surface p-5 shadow-sm">

                    <header class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary dark:bg-sp-brand-dark dark:text-sp-success">
                                <x-stockpilot.icon name="products" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                                    {{ __('Catalog') }}
                                </p>
                                <h2 class="truncate text-base font-extrabold text-sp-text">
                                    {{ __('Products') }}
                                </h2>
                            </div>
                        </div>

                        <span class="rounded-full bg-sp-surface-muted px-3 py-1 text-[11px] font-extrabold uppercase tracking-[0.06em] text-sp-text-muted">
                            {{ trans_choice(':count item|:count items', $products->count(), ['count' => $products->count()]) }}
                        </span>
                    </header>

                    <div class="mt-4">
                        <label for="pos-product-search" class="sr-only">{{ __('Search products') }}</label>

                        <div class="relative">
                            <svg class="pointer-events-none absolute start-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-sp-text-subtle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" stroke-linecap="round" />
                            </svg>

                            <input
                                id="pos-product-search"
                                x-ref="posProductSearch"
                                type="search"
                                wire:model.live.debounce.300ms="search"
                                maxlength="150"
                                placeholder="{{ __('Search products by name, SKU or code…') }}"
                                autocomplete="off"
                                class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface py-3 ps-11 pe-16 text-sm font-semibold text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface-soft"
                            />

                            <span class="pointer-events-none absolute end-3 top-1/2 hidden -translate-y-1/2 text-[10px] font-bold uppercase tracking-wide text-sp-text-subtle sm:block">
                                Ctrl K
                            </span>
                        </div>
                    </div>

                    @if ($categories->isNotEmpty())
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                wire:click="selectCategory(0)"
                                @class([
                                    'rounded-full border px-3 py-1.5 text-xs font-bold transition',
                                    'border-sp-primary bg-sp-primary/10 text-sp-primary dark:text-sp-success' => $categoryId === 0,
                                    'border-sp-border-strong bg-sp-surface text-sp-text-muted hover:border-sp-primary/40 hover:text-sp-primary' => $categoryId !== 0,
                                ])
                            >
                                {{ __('All') }}
                            </button>

                            @foreach ($categories as $category)
                                <button
                                    type="button"
                                    wire:key="pos-category-{{ $category->id }}"
                                    wire:click="selectCategory({{ $category->id }})"
                                    @class([
                                        'rounded-full border px-3 py-1.5 text-xs font-bold transition',
                                        'border-sp-primary bg-sp-primary/10 text-sp-primary dark:text-sp-success' => $categoryId === $category->id,
                                        'border-sp-border-strong bg-sp-surface text-sp-text-muted hover:border-sp-primary/40 hover:text-sp-primary' => $categoryId !== $category->id,
                                    ])
                                >
                                    {{ $category->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif

                    @if ($products->isEmpty())
                        <div class="mt-4">
                            <x-stockpilot.empty-state
                                icon="products"
                                :title="__('No products found')"
                                :message="__('Try a different search term or clear the category filter.')"
                            />
                        </div>
                    @else
                        <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            @foreach ($products as $product)
                                @php
                                    $available = (float) $product->quantity;
                                    $reorder = (float) $product->reorder_level;
                                    $state = $available <= 0 ? 'out' : ($available <= $reorder ? 'low' : 'in');
                                @endphp

                                <button
                                    type="button"
                                    wire:key="pos-product-{{ $product->id }}"
                                    wire:click="addProduct({{ $product->id }})"
                                    @disabled($available <= 0)
                                    class="group flex flex-col rounded-2xl border border-sp-border-strong/60 bg-sp-surface p-4 text-start transition hover:-translate-y-0.5 hover:border-sp-primary/50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sp-primary/20 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-bold leading-snug text-sp-text">{{ $product->name }}</span>

                                        <span class="shrink-0 rounded-md bg-sp-surface-muted px-1.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wide text-sp-text-subtle">
                                            {{ $product->sku }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-lg font-black tabular-nums text-sp-primary dark:text-sp-success">
                                        Rs {{ number_format((float) $product->selling_price, 2) }}
                                    </p>

                                    <div class="mt-3 flex items-center justify-between gap-2">
                                        <span @class([
                                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-[0.06em]',
                                            'bg-sp-success-soft text-sp-success' => $state === 'in',
                                            'bg-sp-warning-soft text-sp-warning-foreground dark:text-sp-warning' => $state === 'low',
                                            'bg-sp-danger-soft text-sp-danger' => $state === 'out',
                                        ])>
                                            <span @class([
                                                'h-1.5 w-1.5 rounded-full',
                                                'bg-sp-success' => $state === 'in',
                                                'bg-sp-warning' => $state === 'low',
                                                'bg-sp-danger' => $state === 'out',
                                            ])></span>
                                            {{ $state === 'in' ? __('In stock') : ($state === 'low' ? __('Low stock') : __('Out of stock')) }}
                                        </span>

                                        <span class="text-xs font-bold tabular-nums text-sp-text-muted">
                                            {{ number_format($available, 0) }} {{ __('on hand') }}
                                        </span>
                                    </div>

                                    <span
                                        @class([
                                            'mt-3 inline-flex items-center justify-center gap-1.5 rounded-lg px-2.5 py-2 text-xs font-extrabold transition',
                                            'bg-sp-primary/10 text-sp-primary group-hover:bg-sp-primary group-hover:text-white dark:text-sp-success dark:group-hover:text-white' => $state !== 'out',
                                            'bg-sp-surface-muted text-sp-text-subtle' => $state === 'out',
                                        ])
                                    >
                                        <x-stockpilot.icon :name="$state === 'out' ? 'x-circle' : 'plus'" class="h-3.5 w-3.5" />
                                        {{ $state === 'out' ? __('Out of stock') : __('Add to sale') }}
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </section>

                
                <section class="rounded-2xl border border-sp-border-strong/60 bg-sp-surface p-5 shadow-sm">
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                        {{ __('Shortcuts') }}
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <button
                            type="button"
                            wire:click="resetSale"
                            class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-3 text-start transition hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted"
                        >
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary transition group-hover:scale-110 dark:bg-sp-brand-dark dark:text-sp-success">
                                <x-stockpilot.icon name="plus" class="h-5 w-5" />
                            </span>
                            <span class="min-w-0">
                                <span class="block text-sm font-extrabold text-sp-text">{{ __('New sale') }}</span>
                                <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Clear the current cart') }}</span>
                            </span>
                        </button>

                        @can('viewAny', App\Models\Invoice::class)
                            <a
                                href="{{ route('admin.sales.index') }}"
                                wire:navigate
                                class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-3 transition hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted"
                            >
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-info-soft text-sp-info-foreground transition group-hover:scale-110 dark:text-sp-info">
                                    <x-stockpilot.icon name="receipt" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-extrabold text-sp-text">{{ __('Sales history') }}</span>
                                    <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Review past invoices') }}</span>
                                </span>
                            </a>
                        @endcan

                        @can('viewAny', App\Models\Invoice::class)
                            <a
                                href="{{ route('admin.reports.index') }}"
                                wire:navigate
                                class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-3 transition hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted"
                            >
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-accent/15 text-sp-accent transition group-hover:scale-110">
                                    <x-stockpilot.icon name="reports" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-extrabold text-sp-text">{{ __('View reports') }}</span>
                                    <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Analyse performance') }}</span>
                                </span>
                            </a>
                        @endcan

                        @can('viewAny', App\Models\Customer::class)
                            <a
                                href="{{ route('admin.customers.index') }}"
                                wire:navigate
                                class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-3 transition hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted"
                            >
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-info-soft text-sp-info-foreground transition group-hover:scale-110 dark:text-sp-info">
                                    <x-stockpilot.icon name="customers" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block text-sm font-extrabold text-sp-text">{{ __('Customers') }}</span>
                                    <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Manage customer records') }}</span>
                                </span>
                            </a>
                        @endcan
                    </div>
                </section>

            </div>

            
            <div class="flex flex-col overflow-hidden rounded-2xl border border-sp-border-strong/60 bg-sp-surface shadow-lg shadow-sp-brand-dark/5 lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)]">

                <div class="flex items-center justify-between border-b border-sp-border-strong/60 px-5 py-4">
                    <div>
                        <h2 class="text-sm font-extrabold uppercase tracking-[0.08em] text-sp-text">
                            {{ __('Current sale') }}
                        </h2>

                        <p class="mt-0.5 text-xs font-semibold text-sp-text-muted">
                            {{ trans_choice(':count item|:count items', count($this->cart), ['count' => count($this->cart)]) }}
                        </p>
                    </div>

                    <button
                        type="button"
                        wire:click="clearCart"
                        @disabled(count($this->cart) === 0)
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-sp-text-subtle transition hover:bg-sp-danger-soft hover:text-sp-danger disabled:cursor-not-allowed disabled:opacity-40"
                        aria-label="{{ __('Clear the cart') }}"
                    >
                        <x-stockpilot.icon name="trash-2" class="h-4 w-4" />
                    </button>
                </div>

                
                <div class="min-h-[160px] flex-1 overflow-y-auto px-5 py-4 lg:max-h-[34vh]">
                    @if (empty($cartLines))
                        <div class="flex h-full min-h-[150px] flex-col items-center justify-center rounded-2xl border border-dashed border-sp-border-strong text-center">
                            <x-stockpilot.icon name="shopping-cart" class="h-9 w-9 text-sp-text-subtle" />
                            <p class="mt-3 text-sm font-semibold text-sp-text-muted">
                                {{ __('Your cart is empty.') }}
                            </p>
                            <p class="mt-1 text-xs text-sp-text-subtle">
                                {{ __('Tap a product to add it to the sale.') }}
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach ($cartLines as $line)
                                <div
                                    wire:key="pos-line-{{ $line['product_id'] }}"
                                    class="rounded-2xl border border-sp-border-strong/60 bg-sp-surface-muted/40 p-3"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-bold text-sp-text">{{ $line['name'] }}</p>
                                            <p class="text-xs font-semibold text-sp-text-muted">{{ $line['sku'] }}</p>
                                        </div>

                                        <button
                                            type="button"
                                            wire:click="removeFromCart({{ $line['product_id'] }})"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sp-text-subtle transition hover:bg-sp-danger-soft hover:text-sp-danger"
                                            aria-label="{{ __('Remove from cart') }}"
                                        >
                                            <x-stockpilot.icon name="trash-2" class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <div class="mt-2.5 grid grid-cols-[86px_96px_1fr] items-center gap-2">

                                        <div class="flex items-center rounded-xl border border-sp-border-strong bg-sp-surface">
                                            <button
                                                type="button"
                                                wire:click="decrementQuantity({{ $line['product_id'] }})"
                                                class="flex h-9 w-8 shrink-0 items-center justify-center text-sm font-extrabold text-sp-text-muted transition hover:text-sp-text"
                                                aria-label="{{ __('Decrease quantity') }}"
                                            >−</button>

                                            <input
                                                type="number"
                                                min="0.001"
                                                step="0.001"
                                                wire:model.blur="cart.{{ $line['product_id'] }}.quantity"
                                                class="h-9 min-w-0 flex-1 bg-transparent text-center text-sm font-bold tabular-nums text-sp-text outline-none"
                                                aria-label="{{ __('Quantity') }}"
                                            />

                                            <button
                                                type="button"
                                                wire:click="incrementQuantity({{ $line['product_id'] }})"
                                                class="flex h-9 w-8 shrink-0 items-center justify-center text-sm font-extrabold text-sp-text-muted transition hover:text-sp-text"
                                                aria-label="{{ __('Increase quantity') }}"
                                            >+</button>
                                        </div>

                                        <div class="relative">
                                            <span class="pointer-events-none absolute start-3 top-1/2 -translate-y-1/2 text-xs font-bold text-sp-text-subtle">Rs</span>
                                            <input
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                wire:model.blur="cart.{{ $line['product_id'] }}.unit_price"
                                                class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface py-2 ps-8 pe-2.5 text-right text-sm font-bold tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                                                aria-label="{{ __('Unit price') }}"
                                            />
                                        </div>

                                        <div class="text-end">
                                            <p class="text-[10px] font-bold uppercase tracking-wide text-sp-text-subtle">
                                                {{ __('Line total') }}
                                            </p>
                                            <p class="text-sm font-extrabold tabular-nums text-sp-text">
                                                Rs {{ number_format((float) $line['line_total'], 2) }}
                                            </p>
                                        </div>
                                    </div>

                                    @if (! $line['has_stock'])
                                        <p class="mt-1.5 text-xs font-semibold text-sp-danger">
                                            {{ __('Insufficient stock.') }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                
                <div class="border-t border-sp-border-strong/60 px-5 py-4">

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="pos-discount" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Discount') }}
                            </label>
                            <div class="flex gap-2">
                                <select
                                    wire:model.blur="discountType"
                                    class="flex-1 rounded-xl border border-sp-border-strong bg-sp-surface px-3 py-2 text-sm font-semibold text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                                >
                                    <option value="fixed">{{ __('Fixed') }}</option>
                                    <option value="percent">{{ __('Percent') }}</option>
                                </select>
                                <input
                                    id="pos-discount"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    wire:model.blur="discountAmount"
                                    class="flex-1 rounded-xl border border-sp-border-strong bg-sp-surface px-3 py-2 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                                    @if($this->discountType === 'percent')
                                        max="100"
                                    @endif
                                />
                            </div>
                        </div>

                        <div>
                            <label for="pos-tax" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Tax rate %') }}
                            </label>
                            <input
                                id="pos-tax"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                wire:model.blur="taxRate"
                                class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface px-3 py-2 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                            />
                        </div>
                    </div>

                    <div class="mt-4 space-y-1.5 text-sm">
                        <div class="flex items-center justify-between text-sp-text-muted">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="font-semibold tabular-nums text-sp-text">Rs {{ number_format($this->subtotalAmount(), 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-sp-text-muted">
                            <span>{{ __('Discount') }}</span>
                            <span class="font-semibold tabular-nums text-sp-text">− Rs {{ number_format($this->toDisplayDiscount(), 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-sp-text-muted">
                            <span>{{ __('Tax') }}</span>
                            <span class="font-semibold tabular-nums text-sp-text">+ Rs {{ number_format($this->taxAmount(), 2) }}</span>
                        </div>

                        <div class="mt-2 flex items-center justify-between border-t border-sp-border-strong/60 pt-2.5">
                            <span class="text-sm font-extrabold uppercase tracking-[0.08em] text-sp-text">
                                {{ __('Total') }}
                            </span>
                            <span class="text-xl font-black tabular-nums text-sp-primary dark:text-sp-success">
                                Rs {{ number_format($this->totalAmount(), 2) }}
                            </span>
                        </div>
                    </div>

                    
                    <div class="mt-5">
                        <p class="mb-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Payment method') }}
                        </p>

                        <div class="grid grid-cols-2 gap-2">
                            @foreach (['cash' => 'Cash', 'card' => 'Card', 'credit' => 'Credit', 'bank_transfer' => 'Bank transfer'] as $method => $label)
                                <button
                                    type="button"
                                    wire:key="pos-method-{{ $method }}"
                                    wire:click="setPaymentMethod('{{ $method }}')"
                                    @class([
                                        'rounded-xl border px-3 py-2.5 text-xs font-bold transition focus:outline-none focus:ring-2 focus:ring-sp-primary/20',
                                        'border-sp-primary bg-sp-primary/10 text-sp-primary dark:text-sp-success' => $this->paymentMethod === $method,
                                        'border-sp-border-strong bg-sp-surface text-sp-text-muted hover:bg-sp-surface-muted' => $this->paymentMethod !== $method,
                                    ])
                                >
                                    {{ __($label) }}
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <label for="pos-tendered" class="mb-1 block text-[11px] font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Amount received') }}
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute start-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-sp-text-subtle">Rs</span>
                                <input
                                    id="pos-tendered"
                                    type="number"
                                    min="0.01"
                                    step="0.01"
                                    wire:model.blur="tendered"
                                    class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface py-2.5 ps-9 pe-3.5 text-right text-base font-extrabold tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                                />
                            </div>

                            <div class="mt-2 grid grid-cols-4 gap-1.5">
                                <button
                                    type="button"
                                    wire:click="tenderedExact"
                                    class="rounded-lg border border-sp-border-strong bg-sp-surface px-2 py-1.5 text-[11px] font-extrabold text-sp-text-muted transition hover:bg-sp-surface-muted dark:bg-sp-surface"
                                >
                                    {{ __('Exact') }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="tenderedAdd(50)"
                                    class="rounded-lg border border-sp-border-strong bg-sp-surface px-2 py-1.5 text-[11px] font-extrabold text-sp-text-muted transition hover:bg-sp-surface-muted dark:bg-sp-surface"
                                >
                                    +50
                                </button>
                                <button
                                    type="button"
                                    wire:click="tenderedAdd(100)"
                                    class="rounded-lg border border-sp-border-strong bg-sp-surface px-2 py-1.5 text-[11px] font-extrabold text-sp-text-muted transition hover:bg-sp-surface-muted dark:bg-sp-surface"
                                >
                                    +100
                                </button>
                                <button
                                    type="button"
                                    wire:click="tenderedAdd(500)"
                                    class="rounded-lg border border-sp-border-strong bg-sp-surface px-2 py-1.5 text-[11px] font-extrabold text-sp-text-muted transition hover:bg-sp-surface-muted dark:bg-sp-surface"
                                >
                                    +500
                                </button>
                            </div>

                            <div class="mt-2 flex items-center justify-between text-sm">
                                <span class="font-semibold text-sp-text-muted">{{ __('Change') }}</span>
                                <span class="font-extrabold tabular-nums text-sp-success">
                                    Rs {{ number_format($this->changeAmount(), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="completeSale"
                        wire:loading.attr="disabled"
                        wire:target="completeSale"
                        @disabled(count($this->cart) === 0)
                        class="mt-5 flex w-full items-center justify-center gap-2 rounded-2xl bg-sp-primary px-5 py-3.5 text-base font-extrabold text-sp-primary-foreground shadow-lg shadow-sp-primary/15 transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 disabled:cursor-not-allowed disabled:opacity-45"
                    >
                        <x-stockpilot.icon name="check" class="h-5 w-5" />
                        <span wire:loading.remove wire:target="completeSale">
                            {{ __('Complete sale') }}
                        </span>
                        <span wire:loading wire:target="completeSale">
                            {{ __('Processing…') }}
                        </span>
                    </button>
                </div>

            </div>

        </div>
    @endif

    
    <x-stockpilot.modal
        open="$wire.showQuickCustomer"
        close="$wire.hideQuickCustomer()"
        :title="__('Quick customer')"
        :subtitle="__('Save and continue without leaving the sale.')"
        :eyebrow="__('Customer')"
        icon="customers"
        width="max-w-lg"
    >
        <div class="space-y-3 px-6 py-5">

            <div>
                <label for="pos-quick-name" class="mb-1 block text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                    {{ __('Full name') }} *
                </label>
                <input
                    id="pos-quick-name"
                    type="text"
                    wire:model.blur="quickName"
                    maxlength="150"
                    autocomplete="off"
                    class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                />
                @error('quickName') <p class="mt-1 text-xs font-semibold text-sp-danger">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label for="pos-quick-phone" class="mb-1 block text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Phone') }}
                    </label>
                    <input
                        id="pos-quick-phone"
                        type="text"
                        wire:model.blur="quickPhone"
                        maxlength="30"
                        autocomplete="off"
                        class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                    />
                    @error('quickPhone') <p class="mt-1 text-xs font-semibold text-sp-danger">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="pos-quick-email" class="mb-1 block text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Email') }}
                    </label>
                    <input
                        id="pos-quick-email"
                        type="email"
                        wire:model.blur="quickEmail"
                        maxlength="150"
                        autocomplete="off"
                        class="block w-full rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:bg-sp-surface"
                    />
                    @error('quickEmail') <p class="mt-1 text-xs font-semibold text-sp-danger">{{ $message }}</p> @enderror
                </div>
            </div>

        </div>

        <x-slot:footer>
            <button
                type="button"
                wire:click="hideQuickCustomer"
                class="inline-flex items-center justify-center rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text transition hover:bg-sp-surface-muted"
            >
                {{ __('Cancel') }}
            </button>

            <button
                type="button"
                wire:click="saveQuickCustomer"
                wire:loading.attr="disabled"
                wire:target="saveQuickCustomer"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover disabled:opacity-60"
            >
                <x-stockpilot.icon name="check" class="h-4 w-4" />
                {{ __('Save & continue') }}
            </button>
        </x-slot:footer>
    </x-stockpilot.modal>

    
    <script>
        document.addEventListener('livewire:init', () => {
            if (window.__stockPilotPosToastsBound) {
                return;
            }

            window.__stockPilotPosToastsBound = true;

            Livewire.on('stockpilot-toast', (payload) => {
                const data = Array.isArray(payload) ? (payload[0] ?? {}) : (payload ?? {});

                if (window.StockPilotSwal) {
                    window.StockPilotSwal.toast(data.icon ?? 'info', data.title ?? '');
                }
            });
        });
    </script>

</x-stockpilot.admin-page>

