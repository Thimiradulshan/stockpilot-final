<x-stockpilot.page-table mobile="lg" aria-label="{{ __('Purchase history') }}">

    <table class="min-w-full border-collapse">

        <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">

            <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Purchase') }}
                </th>

                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Supplier') }}
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

                <th scope="col" class="w-[200px] px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                    {{ __('Actions') }}
                </th>

            </tr>

        </thead>

        <tbody class="divide-y divide-sp-border dark:divide-sp-border">

            @forelse ($purchases as $purchase)

                <tr
                    wire:key="purchase-row-{{ $purchase->id }}"
                    class="group transition duration-150 odd:bg-sp-surface even:bg-sp-surface-muted/40 hover:bg-sp-primary/[0.04] dark:odd:bg-sp-surface dark:even:bg-sp-surface-muted dark:hover:bg-sp-brand-dark/70"
                >

                    <td class="px-6 py-5">

                        <div class="flex min-w-0 items-center gap-3">

                            <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary shadow-sm dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

                                <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-sp-accent/25"></span>

                                <x-stockpilot.icon name="shopping-cart" class="relative h-5 w-5" />

                            </div>

                            <div class="min-w-0">

                                <p class="truncate text-[15px] font-extrabold text-sp-text">
                                    {{ $purchase->purchase_number }}
                                </p>

                                <p class="mt-1 truncate text-xs font-semibold text-sp-text-subtle">
                                    {{ $purchase->createdBy?->name ?? 'System' }}
                                </p>

                            </div>

                        </div>

                    </td>

                    <td class="px-6 py-5">

                        <div class="min-w-[180px]">

                            <p class="font-semibold text-sp-text">
                                {{ $purchase->supplier?->name ?? '—' }}
                            </p>

                            @if ($purchase->supplier?->company)
                                <p class="mt-1 text-xs font-semibold text-sp-text-subtle">
                                    {{ $purchase->supplier->company }}
                                </p>
                            @endif

                        </div>

                    </td>

                    <td class="px-6 py-5">

                        <p class="text-sm font-semibold text-sp-text">
                            {{ $purchase->purchase_date?->format('d M Y') ?? '—' }}
                        </p>

                        <p class="mt-1 text-xs font-semibold text-sp-text-subtle">
                            {{ $purchase->purchase_date?->format('l') ?? '' }}
                        </p>

                    </td>

                    <td class="px-6 py-5 text-center">

                        <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-sp-info-soft px-3 py-2 text-sm font-extrabold text-sp-info-foreground shadow-sm dark:bg-sp-info-soft dark:text-sp-info">
                            {{ $purchase->items_count }}
                        </span>

                    </td>

                    <td class="px-6 py-5 text-right">

                        <p class="text-sm font-extrabold tabular-nums text-sp-text">
                            Rs {{ number_format((float) $purchase->total_amount, 2) }}
                        </p>

                        @if ((float) $purchase->discount_amount > 0 || (float) $purchase->tax_amount > 0)
                            <div class="mt-1 space-y-0.5 text-xs font-semibold text-sp-text-subtle">
                                @if ((float) $purchase->discount_amount > 0)
                                    <p>{{ __('Discount') }}: Rs {{ number_format((float) $purchase->discount_amount, 2) }}</p>
                                @endif

                                @if ((float) $purchase->tax_amount > 0)
                                    <p>{{ __('Tax') }}: Rs {{ number_format((float) $purchase->tax_amount, 2) }}</p>
                                @endif
                            </div>
                        @endif

                    </td>

                    <td class="px-6 py-5 text-center">

                        @if ($purchase->status === 'completed')
                            <x-stockpilot.status-badge :label="__('Completed')" tone="success" />
                        @elseif ($purchase->status === 'cancelled')
                            <x-stockpilot.status-badge :label="__('Cancelled')" tone="danger" />
                        @else
                            <x-stockpilot.status-badge :label="$purchase->status" tone="muted" />
                        @endif

                    </td>

                    <td class="px-6 py-5">

                        <div class="flex flex-wrap items-center gap-2">

                            <button
                                type="button"
                                @click="openView(@js($purchase))"
                                class="inline-flex items-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm font-extrabold text-sp-text shadow-sm transition hover:border-sp-primary/30 hover:bg-sp-primary/10 hover:text-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text dark:hover:bg-sp-surface-muted dark:hover:text-sp-success"
                            >
                                <x-stockpilot.icon name="eye" class="h-4 w-4" />
                                {{ __('View') }}
                            </button>

                            @can('cancel', $purchase)
                                @if ($purchase->status === 'completed')
                                    <button
                                        type="button"
                                        @click="openCancel(@js($purchase))"
                                        class="inline-flex items-center gap-2 rounded-xl border border-sp-danger/30 bg-sp-danger-soft px-3.5 py-2.5 text-sm font-extrabold text-sp-danger shadow-sm transition hover:bg-sp-danger-soft focus:outline-none focus:ring-2 focus:ring-sp-danger/20 dark:border-sp-danger/30 dark:bg-sp-danger-soft dark:text-sp-danger"
                                    >
                                        <x-stockpilot.icon name="x-circle" class="h-4 w-4" />
                                        {{ __('Cancel') }}
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
                            icon="shopping-cart"
                            :title="$this->hasActiveFilters() ? __('No matching purchases') : __('No purchases yet')"
                            :message="$this->hasActiveFilters() ? __('Try changing your search or status filter.') : __('Create your first purchase to start tracking incoming stock.')"
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

                            @can('create', \App\Models\Purchase::class)
                                @if (! $this->hasActiveFilters())
                                    <button
                                        type="button"
                                        @click="openCreate()"
                                        class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-md transition hover:bg-sp-primary-hover"
                                    >
                                        {{ __('New purchase') }}
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