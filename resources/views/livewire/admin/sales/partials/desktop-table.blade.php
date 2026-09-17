<x-stockpilot.page-table mobile="lg" aria-label="{{ __('Sales history') }}">

    <table class="min-w-full border-collapse">

        <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">

            <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Invoice') }}
                </th>

                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Customer') }}
                </th>

                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Date') }}
                </th>

                <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Items') }}
                </th>

                <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Total') }}
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

            @forelse ($invoices as $invoice)

                <tr
                    wire:key="invoice-row-{{ $invoice->id }}"
                    class="group transition duration-150 odd:bg-sp-surface even:bg-sp-surface-muted/40 hover:bg-sp-primary/[0.04] dark:odd:bg-sp-surface dark:even:bg-sp-surface-muted dark:hover:bg-sp-brand-dark/70"
                >

                    <td class="px-6 py-5">

                        <div class="flex min-w-0 items-center gap-3">

                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary shadow-sm dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

                                <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-sp-accent/25"></span>

                                <x-stockpilot.icon name="receipt" class="relative h-5 w-5" />

                            </div>

                            <div class="min-w-0">

                                <p class="truncate text-[15px] font-extrabold text-sp-text">
                                    {{ $invoice->invoice_number }}
                                </p>

                                <p class="mt-1 truncate text-xs font-semibold text-sp-text-subtle">
                                    {{ $invoice->createdBy?->name ?? 'System' }}
                                </p>

                            </div>

                        </div>

                    </td>

                    <td class="px-6 py-5">

                        <div class="min-w-[180px]">

                            <p class="font-semibold text-sp-text">
                                {{ $invoice->customer?->name ?? '—' }}
                            </p>

                            @if ($invoice->customer?->phone)
                                <p class="mt-1 text-xs font-semibold text-sp-text-subtle">
                                    {{ $invoice->customer->phone }}
                                </p>
                            @endif

                        </div>

                    </td>

                    <td class="px-6 py-5">

                        <p class="text-sm font-semibold text-sp-text">
                            {{ $invoice->invoice_date?->format('d M Y') ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs font-semibold text-sp-text-subtle">
                            {{ $invoice->invoice_date?->format('l') ?? '' }}
                        </p>

                    </td>

                    <td class="px-6 py-5 text-center">

                        <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-sp-info-soft px-3 py-2 text-sm font-extrabold text-sp-info-foreground shadow-sm dark:bg-sp-info-soft dark:text-sp-info">
                            {{ $invoice->items_count }}
                        </span>

                    </td>

                    <td class="px-6 py-5 text-right">

                        <p class="text-sm font-extrabold tabular-nums text-sp-text">
                            Rs {{ number_format((float) $invoice->total_amount, 2) }}
                        </p>

                        @if ((float) $invoice->discount_amount > 0 || (float) $invoice->tax_amount > 0)
                            <div class="mt-1 space-y-0.5 text-xs font-semibold text-sp-text-subtle">
                                @if ((float) $invoice->discount_amount > 0)
                                    <p>{{ __('Discount') }}: Rs {{ number_format((float) $invoice->discount_amount, 2) }}</p>
                                @endif

                                @if ((float) $invoice->tax_amount > 0)
                                    <p>{{ __('Tax') }}: Rs {{ number_format((float) $invoice->tax_amount, 2) }}</p>
                                @endif
                            </div>
                        @endif

                    </td>

                    <td class="px-6 py-5 text-center">

                        <div class="flex flex-col items-center gap-1.5">
                            @if ($invoice->status === 'completed')
                                <x-stockpilot.status-badge :label="__('Completed')" tone="success" />
                            @elseif ($invoice->status === 'voided')
                                <x-stockpilot.status-badge :label="__('Voided')" tone="danger" />
                            @else
                                <x-stockpilot.status-badge :label="$invoice->status" tone="muted" />
                            @endif

                            @if ($invoice->status === 'completed')
                                @if ($invoice->payment_status === 'paid')
                                    <x-stockpilot.status-badge :label="__('Paid')" tone="success" />
                                @elseif ($invoice->payment_status === 'partially_paid')
                                    <x-stockpilot.status-badge :label="__('Partially paid')" tone="info" />
                                @else
                                    <x-stockpilot.status-badge :label="__('Unpaid')" tone="warning" />
                                @endif
                            @endif
                        </div>

                    </td>

                    <td class="px-6 py-5">

                        <div class="flex flex-wrap items-center gap-2">

                            <button
                                type="button"
                                @click="openView(@js($invoice))"
                                class="inline-flex items-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm font-extrabold text-sp-text shadow-sm transition hover:border-sp-primary/30 hover:bg-sp-primary/10 hover:text-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text dark:hover:bg-sp-surface-muted dark:hover:text-sp-success"
                            >
                                <x-stockpilot.icon name="eye" class="h-4 w-4" />
                                {{ __('View') }}
                            </button>

                            @can('pay', $invoice)
                                @if ($invoice->status === 'completed' && $invoice->payment_status !== 'paid')
                                    <button
                                        type="button"
                                        @click="openPayment(@js($invoice))"
                                        class="inline-flex items-center gap-2 rounded-xl border border-sp-info/30 bg-sp-info-soft px-3.5 py-2.5 text-sm font-extrabold text-sp-info-foreground shadow-sm transition hover:bg-sp-info-soft focus:outline-none focus:ring-2 focus:ring-sp-info/20 dark:border-sp-info/30 dark:bg-sp-info-soft dark:text-sp-info"
                                    >
                                        <x-stockpilot.icon name="credit-card" class="h-4 w-4" />
                                        {{ __('Pay') }}
                                    </button>
                                @endif
                            @endcan

                            @can('void', $invoice)
                                @if ($invoice->status === 'completed')
                                    <button
                                        type="button"
                                        @click="openVoid(@js($invoice))"
                                        class="inline-flex items-center gap-2 rounded-xl border border-sp-danger/30 bg-sp-danger-soft px-3.5 py-2.5 text-sm font-extrabold text-sp-danger shadow-sm transition hover:bg-sp-danger-soft focus:outline-none focus:ring-2 focus:ring-sp-danger/20 dark:border-sp-danger/30 dark:bg-sp-danger-soft dark:text-sp-danger"
                                    >
                                        <x-stockpilot.icon name="x-circle" class="h-4 w-4" />
                                        {{ __('Void') }}
                                    </button>
                                @endif
                            @endcan

                        </div>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="7" class="px-6 py-20 text-center">

                        <x-stockpilot.empty-state
                            icon="receipt"
                            :title="$this->hasActiveFilters() ? __('No matching invoices') : __('No sales yet')"
                            :message="$this->hasActiveFilters() ? __('Try changing your search or status filter.') : __('Create your first sale to start tracking customer invoices.')"
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

                            @can('create', \App\Models\Invoice::class)
                                @if (! $this->hasActiveFilters())
                                    <button
                                        type="button"
                                        @click="openCreate()"
                                        class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-md transition hover:bg-sp-primary-hover"
                                    >
                                        {{ __('New sale') }}
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