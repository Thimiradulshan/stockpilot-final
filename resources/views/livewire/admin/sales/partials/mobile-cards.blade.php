<div class="space-y-3 lg:hidden">

    @forelse ($invoices as $invoice)

        <article
            wire:key="mobile-invoice-{{ $invoice->id }}"
            class="rounded-2xl border border-sp-border bg-sp-surface p-4 shadow-sm dark:border-sp-border dark:bg-sp-surface"
        >

            <div class="flex items-start justify-between gap-3">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">
                        <x-stockpilot.icon name="receipt" class="h-5 w-5" />
                    </div>

                    <div class="min-w-0">

                        <p class="truncate text-sm font-bold text-sp-text">
                            {{ $invoice->invoice_number }}
                        </p>

                        <p class="mt-0.5 text-xs font-semibold text-sp-text-subtle">
                            {{ $invoice->invoice_date?->format('d M Y') ?? '—' }}
                        </p>

                    </div>

                </div>

                <div class="flex flex-col items-end gap-1.5">
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

            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">

                <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Customer') }}
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-sp-text">
                        {{ $invoice->customer?->name ?? '—' }}
                    </p>

                    @if ($invoice->customer?->phone)
                        <p class="mt-0.5 truncate text-xs text-sp-text-subtle">
                            {{ $invoice->customer->phone }}
                        </p>
                    @endif
                </div>

                <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Items') }}
                    </p>

                    <p class="mt-1 text-lg font-bold text-sp-text">
                        {{ $invoice->items_count }}
                    </p>
                </div>

            </div>

            <div class="mt-4 grid grid-cols-2 gap-3">

                <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Total') }}
                    </p>

                    <p class="mt-1 text-base font-extrabold tabular-nums text-sp-text">
                        Rs {{ number_format((float) $invoice->total_amount, 2) }}
                    </p>
                </div>

                <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Created by') }}
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-sp-text">
                        {{ $invoice->createdBy?->name ?? 'System' }}
                    </p>
                </div>

            </div>

            @if ((float) $invoice->discount_amount > 0 || (float) $invoice->tax_amount > 0)
                <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs font-semibold text-sp-text-subtle">
                    @if ((float) $invoice->discount_amount > 0)
                        <span>{{ __('Discount') }}: Rs {{ number_format((float) $invoice->discount_amount, 2) }}</span>
                    @endif

                    @if ((float) $invoice->tax_amount > 0)
                        <span>{{ __('Tax') }}: Rs {{ number_format((float) $invoice->tax_amount, 2) }}</span>
                    @endif
                </div>
            @endif

            <div class="mt-4 grid grid-cols-2 gap-2">

                <button
                    type="button"
                    @click="openView(@js($invoice))"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
                >
                    <x-stockpilot.icon name="eye" class="h-4 w-4" />
                    {{ __('View details') }}
                </button>

                @can('pay', $invoice)
                    @if ($invoice->status === 'completed' && $invoice->payment_status !== 'paid')
                        <button
                            type="button"
                            @click="openPayment(@js($invoice))"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-info/30 bg-sp-info-soft px-4 py-2.5 text-sm font-semibold text-sp-info-foreground transition hover:bg-sp-info-soft"
                        >
                            <x-stockpilot.icon name="credit-card" class="h-4 w-4" />
                            {{ __('Record payment') }}
                        </button>
                    @endif
                @endcan

                @can('void', $invoice)
                    @if ($invoice->status === 'completed')
                        <button
                            type="button"
                            @click="openVoid(@js($invoice))"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-danger/30 bg-sp-danger-soft px-4 py-2.5 text-sm font-semibold text-sp-danger transition hover:bg-sp-danger-soft"
                        >
                            <x-stockpilot.icon name="x-circle" class="h-4 w-4" />
                            {{ __('Void') }}
                        </button>
                    @endif
                @endcan

            </div>

        </article>

    @empty

        <div class="rounded-2xl border border-sp-border bg-sp-surface px-6 py-14 text-center shadow-sm dark:border-sp-border dark:bg-sp-surface">

            <x-stockpilot.empty-state
                icon="receipt"
                :title="__('No sales found')"
                :message="__('Try changing your search or status filter.')"
            />

        </div>

    @endforelse

</div>

<x-stockpilot.pagination-bar
    :paginator="$invoices"
    :item-label="__('invoices')"
/>