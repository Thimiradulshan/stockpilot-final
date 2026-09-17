<x-stockpilot.admin-page>

    @php
        $hasSalesTrend = collect($salesTrend)->sum('value') > 0;

        $statusPills = [
            'success' => 'bg-sp-success-soft text-sp-success',
            'info' => 'bg-sp-info-soft text-sp-info-foreground dark:text-sp-info',
            'warning' => 'bg-sp-warning-soft text-sp-warning-foreground dark:text-sp-warning',
            'danger' => 'bg-sp-danger-soft text-sp-danger',
        ];

        $rowTiles = [
            'success' => 'bg-sp-success-soft text-sp-success',
            'info' => 'bg-sp-info-soft text-sp-info-foreground dark:text-sp-info',
            'warning' => 'bg-sp-warning-soft text-sp-warning-foreground dark:text-sp-warning',
            'danger' => 'bg-sp-danger-soft text-sp-danger',
        ];

        $inventoryTone = $outOfStockCount > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success');
        $inventoryStatus = $outOfStockCount > 0
            ? __('Action needed')
            : ($lowStockCount > 0 ? __('Attention') : __('Healthy'));

        $salesTone = $todayInvoiceCount > 0 ? 'success' : 'info';
        $salesStatus = $todayInvoiceCount > 0 ? __('On track') : __('No sales yet');

        $creditTone = $outstandingBalance > 0 ? 'warning' : 'success';
        $creditStatus = $outstandingBalance > 0 ? __('Unsettled') : __('Settled');
    @endphp

    
    <section class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div class="min-w-0">

            <p class="text-[11px] font-extrabold uppercase tracking-[0.10em] text-sp-text-muted">
                {{ $todayLabel }}
            </p>

            <h1 class="mt-1.5 text-2xl font-black tracking-tight text-sp-text sm:text-3xl">
                {{ $greeting }},
                <span class="bg-gradient-to-r from-sp-primary via-sp-info to-sp-accent bg-clip-text text-transparent">
                    {{ auth()->user()?->name }}
                </span>
            </h1>

            <p class="mt-1 text-sm font-semibold text-sp-text-subtle">
                {{ __('Here is how your business is performing today.') }}
            </p>

        </div>

        <div class="flex flex-wrap items-center gap-3">

            @can('create', \App\Models\Invoice::class)
                <a
                    href="{{ route('admin.pos.index') }}"
                    wire:navigate
                    class="group inline-flex items-center gap-2.5 rounded-xl bg-sp-primary px-5 py-3.5 text-sm font-bold text-sp-primary-foreground shadow-lg shadow-sp-primary/25 transition duration-200 hover:-translate-y-0.5 hover:bg-sp-primary-hover hover:shadow-xl hover:shadow-sp-primary/30 focus:outline-none focus:ring-4 focus:ring-sp-primary/20"
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15">
                        <x-stockpilot.icon name="shopping-cart" class="h-4 w-4 transition-transform duration-200 group-hover:scale-110" />
                    </span>
                    {{ __('New sale') }}
                </a>
            @endcan

            <a
                href="{{ route('admin.reports.index') }}"
                wire:navigate
                class="inline-flex items-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:text-sp-primary focus:outline-none focus:ring-4 focus:ring-sp-primary/10"
            >
                <x-stockpilot.icon name="reports" class="h-4 w-4" />
                {{ __('Reports') }}
            </a>

        </div>

    </section>

    
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">

        @if ($canSeeSales)
            <section
                aria-label="{{ __('Total sales today') }}"
                class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-sp-primary via-sp-primary to-sp-brand-dark p-6 text-white shadow-xl shadow-sp-primary/25 xl:col-span-2"
            >
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 left-1/3 h-52 w-52 rounded-full bg-sp-success/20 blur-3xl" aria-hidden="true"></div>

                <div class="relative">

                    <p class="text-[11px] font-extrabold uppercase tracking-[0.12em] text-white/70">
                        {{ __('Total sales today') }}
                    </p>

                    <p class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">
                        Rs. {{ number_format($todaySalesTotal, 2) }}
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-2">

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold">
                            <x-stockpilot.icon name="receipt" class="h-3.5 w-3.5" />
                            {{ number_format($todayInvoiceCount) }} {{ __('invoices completed') }}
                        </span>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold">
                            <x-stockpilot.icon name="customers" class="h-3.5 w-3.5" />
                            {{ number_format($customersServedToday) }} {{ __('customers served') }}
                        </span>

                    </div>

                    <div class="mt-6 flex h-16 items-end gap-1.5" aria-hidden="true">
                        @foreach ($salesTrend as $bucket)
                            @php($pct = max(6, (int) round(($bucket['value'] / $trendMaxValue) * 100)))
                            <div
                                class="flex-1 rounded-t-md {{ $loop->last ? 'bg-white' : 'bg-white/40' }}"
                                style="height: {{ $pct }}%"
                            ></div>
                        @endforeach
                    </div>

                </div>
            </section>
        @endif

        <x-stockpilot.panel
            :eyebrow="__('Overview')"
            :title="__('Business status')"
            icon="dashboard"
            @class(['xl:col-span-2' => ! $canSeeSales])
        >

            <div class="space-y-3">

                @if ($canSeeStock)
                    <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/60 p-3 dark:bg-sp-surface-muted">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $rowTiles[$inventoryTone] }}">
                            <x-stockpilot.icon name="products" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-extrabold text-sp-text">{{ __('Inventory health') }}</p>
                            <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                {{ number_format($lowStockCount) }} {{ __('low') }} · {{ number_format($outOfStockCount) }} {{ __('out of stock') }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.06em] {{ $statusPills[$inventoryTone] }}">
                            {{ $inventoryStatus }}
                        </span>
                    </div>
                @endif

                @if ($canSeeSales)
                    <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/60 p-3 dark:bg-sp-surface-muted">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $rowTiles[$salesTone] }}">
                            <x-stockpilot.icon name="receipt" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-extrabold text-sp-text">{{ __('Sales') }}</p>
                            <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                {{ number_format($todayInvoiceCount) }} {{ __('invoices today') }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.06em] {{ $statusPills[$salesTone] }}">
                            {{ $salesStatus }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/60 p-3 dark:bg-sp-surface-muted">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $rowTiles[$creditTone] }}">
                            <x-stockpilot.icon name="credit-card" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-extrabold text-sp-text">{{ __('Outstanding credit') }}</p>
                            <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                Rs. {{ number_format($outstandingBalance, 2) }} {{ __('unpaid') }}
                            </p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-[0.06em] {{ $statusPills[$creditTone] }}">
                            {{ $creditStatus }}
                        </span>
                    </div>
                @endif

            </div>

        </x-stockpilot.panel>

    </div>

    
    @if ($canSeeSales)
        <section aria-label="{{ __('Sales KPIs') }}">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                <x-stockpilot.mini-kpi
                    :label="__('Total sales')"
                    :value="'Rs. ' . number_format($totalSales, 2)"
                    :hint="__('All completed invoices')"
                    icon="reports"
                    tone="primary"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Invoices today')"
                    :value="number_format($todayInvoiceCount)"
                    :hint="__('Completed today')"
                    icon="receipt"
                    tone="success"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Customers')"
                    :value="number_format($totalCustomers)"
                    :hint="__('Total customers')"
                    icon="customers"
                    tone="info"
                    :href="route('admin.customers.index')"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Outstanding credit')"
                    :value="'Rs. ' . number_format($outstandingBalance, 2)"
                    :hint="__('Unpaid customer balance')"
                    icon="credit-card"
                    tone="warning"
                />

            </div>
        </section>
    @endif

    @if ($canSeeStock)
        <section aria-label="{{ __('Stock KPIs') }}">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

                <x-stockpilot.mini-kpi
                    :label="__('Stock value')"
                    :value="'Rs. ' . number_format($stockValue, 2)"
                    :hint="__('Active catalog at cost')"
                    icon="products"
                    tone="primary"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Total products')"
                    :value="number_format($totalProducts)"
                    :hint="__('Entire product catalog')"
                    icon="products"
                    tone="info"
                    :href="route('admin.products.index')"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Total suppliers')"
                    :value="number_format($totalSuppliers)"
                    :hint="__('Vendor partners')"
                    icon="suppliers"
                    tone="accent"
                    :href="route('admin.suppliers.index')"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Low stock')"
                    :value="number_format($lowStockCount)"
                    :hint="__('Needs reordering')"
                    icon="alert-triangle"
                    tone="warning"
                    :quiet="$lowStockCount === 0"
                />

                <x-stockpilot.mini-kpi
                    :label="__('Out of stock')"
                    :value="number_format($outOfStockCount)"
                    :hint="__('Immediate attention')"
                    icon="x-circle"
                    tone="danger"
                    :quiet="$outOfStockCount === 0"
                />

            </div>
        </section>
    @endif

    
    @if ($canSeeSales)
        <x-stockpilot.panel
            :eyebrow="__('Last 7 days')"
            :title="__('Sales overview')"
            icon="reports"
        >
            <x-slot:actions>
                <x-stockpilot.link :href="route('admin.reports.index')" class="text-sm font-bold">
                    {{ __('View reports') }}
                </x-stockpilot.link>
            </x-slot:actions>

            @if ($hasSalesTrend)
                <div class="grid grid-cols-7 gap-2 sm:gap-4">
                    @foreach ($salesTrend as $bucket)
                        @php($pct = max(6, (int) round(($bucket['value'] / $trendMaxValue) * 100)))
                        <div class="flex flex-col items-center gap-2">
                            <div class="flex h-40 w-full items-end justify-center sm:h-48">
                                <div
                                    class="w-full rounded-t-lg transition duration-300 {{ $loop->last ? 'bg-gradient-to-t from-sp-primary to-sp-success' : 'bg-sp-primary/30 dark:bg-sp-primary/40' }}"
                                    style="height: {{ $pct }}%"
                                    title="Rs. {{ number_format($bucket['value'], 2) }} · {{ $bucket['date'] }}"
                                ></div>
                            </div>
                            <p class="text-[11px] font-extrabold text-sp-text-muted">{{ $bucket['label'] }}</p>
                            <p class="hidden text-[10px] font-semibold text-sp-text-subtle sm:block">{{ $bucket['date'] }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <x-stockpilot.empty-state
                    icon="reports"
                    :title="__('No sales history yet')"
                    :message="__('Completed sales from the last 7 days will appear here.')"
                />
            @endif

        </x-stockpilot.panel>
    @endif

    
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

        @if ($canSeeSales)
            <x-stockpilot.panel
                :eyebrow="__('Sales')"
                :title="__('Recent invoices')"
                icon="receipt"
            >
                <x-slot:actions>
                    <x-stockpilot.link :href="route('admin.sales.index')" class="text-sm font-bold">
                        {{ __('View all') }}
                    </x-stockpilot.link>
                </x-slot:actions>

                <div class="space-y-2">
                    @forelse ($recentInvoices as $invoice)
                        <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/50 bg-sp-background/50 p-3 transition duration-150 hover:border-sp-primary/40 dark:bg-sp-surface-muted">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary dark:bg-sp-brand-dark dark:text-sp-success">
                                <x-stockpilot.icon name="receipt" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-extrabold text-sp-text">{{ $invoice->invoice_number }}</p>
                                <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                    {{ $invoice->customer?->name ?? '—' }} · {{ $invoice->invoice_date->toFormattedDateString() }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-extrabold text-sp-primary dark:text-sp-success">
                                Rs. {{ number_format((float) $invoice->total_amount, 2) }}
                            </p>
                        </div>
                    @empty
                        <x-stockpilot.empty-state
                            icon="receipt"
                            :title="__('No invoices yet')"
                            :message="__('Create your first sale to see it here.')"
                        />
                    @endforelse
                </div>

            </x-stockpilot.panel>

            <x-stockpilot.panel
                :eyebrow="__('Ledger')"
                :title="__('Recent payments')"
                icon="credit-card"
                tone="success"
            >
                <x-slot:actions>
                    <x-stockpilot.link :href="route('admin.sales.index')" class="text-sm font-bold">
                        {{ __('View all') }}
                    </x-stockpilot.link>
                </x-slot:actions>

                <div class="space-y-2">
                    @forelse ($recentPayments as $payment)
                        <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/50 bg-sp-background/50 p-3 transition duration-150 hover:border-sp-success/40 dark:bg-sp-surface-muted">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-success-soft text-sp-success">
                                <x-stockpilot.icon name="credit-card" class="h-5 w-5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-extrabold text-sp-text">
                                    {{ $payment->invoice?->invoice_number ?? '—' }}
                                </p>
                                <p class="truncate text-xs font-semibold text-sp-text-subtle">
                                    {{ $payment->invoice?->customer?->name ?? '—' }} · {{ $payment->payment_date->toDayDateTimeString() }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-extrabold text-sp-success">
                                Rs. {{ number_format((float) $payment->amount, 2) }}
                            </p>
                        </div>
                    @empty
                        <x-stockpilot.empty-state
                            icon="credit-card"
                            :title="__('No payments yet')"
                            :message="__('Payments received will appear here.')"
                        />
                    @endforelse
                </div>

            </x-stockpilot.panel>
        @endif

    </div>

    
    @if ($canSeeStock && $lowStockProducts->isNotEmpty())
        <x-stockpilot.panel
            :eyebrow="__('Inventory')"
            :title="__('Low stock products')"
            icon="alert-triangle"
            tone="warning"
        >
            <x-slot:actions>
                <x-stockpilot.link :href="route('admin.products.index')" class="text-sm font-bold">
                    {{ __('View all') }}
                </x-stockpilot.link>
            </x-slot:actions>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($lowStockProducts as $product)
                    <div class="flex items-center gap-3 rounded-xl border border-sp-border-strong/50 bg-sp-background/50 p-3 dark:bg-sp-surface-muted">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-warning-soft text-sp-warning-foreground dark:text-sp-warning">
                            <x-stockpilot.icon name="alert-triangle" class="h-5 w-5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-sp-text">{{ $product->name }}</p>
                            <p class="truncate text-xs font-semibold text-sp-text-subtle">{{ $product->sku }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-sp-warning-soft px-2.5 py-1 text-[11px] font-extrabold text-sp-warning-foreground dark:text-sp-warning">
                            {{ number_format((float) $product->quantity, 3) }}
                        </span>
                    </div>
                @endforeach
            </div>

        </x-stockpilot.panel>
    @endif

    
    <x-stockpilot.panel
        :eyebrow="__('Shortcuts')"
        :title="__('Quick actions')"
        icon="plus"
    >
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">

            @can('create', \App\Models\Invoice::class)
                <a href="{{ route('admin.pos.index') }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary transition duration-200 group-hover:scale-110 dark:bg-sp-brand-dark dark:text-sp-success">
                        <x-stockpilot.icon name="shopping-cart" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold text-sp-text">{{ __('New sale') }}</span>
                        <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Open the point of sale') }}</span>
                    </span>
                </a>
            @endcan

            @can('create', \App\Models\Payment::class)
                <a href="{{ route('admin.sales.index') }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-success-soft text-sp-success transition duration-200 group-hover:scale-110">
                        <x-stockpilot.icon name="credit-card" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold text-sp-text">{{ __('Record payment') }}</span>
                        <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Settle a customer invoice') }}</span>
                    </span>
                </a>
            @endcan

            @can('create', \App\Models\Product::class)
                <a href="{{ route('admin.products.index') }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-info-soft text-sp-info-foreground transition duration-200 group-hover:scale-110 dark:text-sp-info">
                        <x-stockpilot.icon name="products" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold text-sp-text">{{ __('Add product') }}</span>
                        <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Grow your catalog') }}</span>
                    </span>
                </a>
            @endcan

            @can('create', \App\Models\Customer::class)
                <a href="{{ route('admin.customers.index') }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-info-soft text-sp-info-foreground transition duration-200 group-hover:scale-110 dark:text-sp-info">
                        <x-stockpilot.icon name="customers" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold text-sp-text">{{ __('Add customer') }}</span>
                        <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Register a new buyer') }}</span>
                    </span>
                </a>
            @endcan

            @can('create', \App\Models\Supplier::class)
                <a href="{{ route('admin.suppliers.index') }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-accent/15 text-sp-accent transition duration-200 group-hover:scale-110">
                        <x-stockpilot.icon name="suppliers" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-extrabold text-sp-text">{{ __('Add supplier') }}</span>
                        <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Manage vendor partners') }}</span>
                    </span>
                </a>
            @endcan

            <a href="{{ route('admin.reports.index') }}" wire:navigate class="group flex items-center gap-3 rounded-xl border border-sp-border-strong/60 bg-sp-background/50 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-sp-primary/40 hover:shadow-md dark:bg-sp-surface-muted">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary transition duration-200 group-hover:scale-110 dark:bg-sp-brand-dark dark:text-sp-success">
                    <x-stockpilot.icon name="reports" class="h-5 w-5" />
                </span>
                <span class="min-w-0">
                    <span class="block text-sm font-extrabold text-sp-text">{{ __('View reports') }}</span>
                    <span class="block truncate text-xs font-semibold text-sp-text-subtle">{{ __('Analyse business performance') }}</span>
                </span>
            </a>

        </div>
    </x-stockpilot.panel>

</x-stockpilot.admin-page>
