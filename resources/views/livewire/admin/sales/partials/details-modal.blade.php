<x-stockpilot.modal
    open="viewOpen"
    close="closeView()"
    :title="__('Invoice details')"
    :subtitle="__('Invoice record and stock movements')"
    :eyebrow="__('Sales')"
    icon="file-text"
    heading-id="view-invoice-title"
    dialog-label="{{ __('Invoice details') }}"
    width="max-w-4xl"
>

    <div class="max-h-[calc(100vh-12rem)] overflow-y-auto">
        <div class="space-y-6 px-6 py-6">

            <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Customer') }}
                    </p>

                    <p class="mt-1 truncate text-sm font-bold text-sp-text" x-text="viewInvoice.customer?.name || '—'"></p>

                    <p x-show="viewInvoice.customer?.phone" class="mt-0.5 truncate text-xs text-sp-text-subtle" x-text="viewInvoice.customer?.phone"></p>
                </div>

                <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Status') }}
                    </p>

                    <span
                        class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize"
                        :class="statusBadgeClasses(viewInvoice.status)"
                        x-text="viewInvoice.status || '—'"
                    ></span>
                </div>

                <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Payment') }}
                    </p>

                    <span
                        class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize"
                        :class="paymentStatusBadgeClasses(viewInvoice.payment_status)"
                        x-text="viewInvoice.payment_status ? viewInvoice.payment_status.replaceAll('_', ' ') : '—'"
                    ></span>
                </div>

                <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">
                    <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Created by') }}
                    </p>

                    <p class="mt-1 truncate text-sm font-bold text-sp-text" x-text="viewInvoice.created_by?.name || viewInvoice.createdBy?.name || 'System'"></p>
                </div>

            </section>

            <section>
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-sp-text">
                        {{ __('Sold products') }}
                    </h3>
                </div>

                <div class="overflow-hidden rounded-2xl border border-sp-border">

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">

                            <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                        {{ __('Product') }}
                                    </th>

                                    <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                        {{ __('Qty') }}
                                    </th>

                                    <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                        {{ __('Unit price') }}
                                    </th>

                                    <th class="px-4 py-3 text-right text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                                        {{ __('Line total') }}
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-sp-border dark:divide-sp-border">
                                <template x-for="item in (viewInvoice.items || [])" :key="item.id">
                                    <tr>
                                        <td class="px-4 py-3">
                                            <p class="text-sm font-semibold text-sp-text" x-text="item.product?.name || 'Product'"></p>

                                            <p x-show="item.product?.sku" class="mt-0.5 text-xs text-sp-text-subtle" x-text="item.product?.sku"></p>
                                        </td>

                                        <td class="px-4 py-3 text-right text-sm tabular-nums text-sp-text" x-text="formatQuantity(item.quantity)"></td>

                                        <td class="px-4 py-3 text-right text-sm tabular-nums text-sp-text">
                                            Rs <span x-text="formatMoney(item.unit_price)"></span>
                                        </td>

                                        <td class="px-4 py-3 text-right text-sm font-bold tabular-nums text-sp-text">
                                            Rs <span x-text="formatMoney(item.line_total)"></span>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="!(viewInvoice.items || []).length">
                                    <td colspan="4" class="px-4 py-8 text-center text-sm text-sp-text-subtle">
                                        {{ __('No invoice items available.') }}
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </div>
            </section>

            <section x-show="(viewInvoice.payments || []).length">
                <div class="mb-3">
                    <h3 class="text-sm font-semibold text-sp-text">
                        {{ __('Payments') }}
                    </h3>
                </div>

                <div class="overflow-hidden rounded-2xl border border-sp-border">
                    <div class="divide-y divide-sp-border">
                        <template x-for="payment in (viewInvoice.payments || [])" :key="payment.id">
                            <div class="flex items-center justify-between gap-3 px-4 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-sp-text" x-text="formatDate(payment.payment_date)"></p>

                                    <p class="mt-0.5 text-xs text-sp-text-subtle" x-text="payment.payment_method || '—'"></p>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm font-extrabold tabular-nums text-sp-text">
                                        Rs <span x-text="formatMoney(payment.amount)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1fr_280px]">
                <div>
                    <h3 class="text-sm font-semibold text-sp-text">
                        {{ __('Notes') }}
                    </h3>

                    <div class="mt-3 min-h-24 rounded-2xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">
                        <p class="whitespace-pre-wrap text-sm leading-6 text-sp-text" x-text="viewInvoice.notes || '{{ __('No notes recorded.') }}'"></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-sp-text-muted">{{ __('Subtotal') }}</span>

                            <span class="font-semibold tabular-nums text-sp-text">
                                Rs <span x-text="formatMoney(viewInvoice.subtotal)"></span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-sp-text-muted">{{ __('Discount') }}</span>

                            <span class="font-semibold tabular-nums text-sp-text">
                                Rs <span x-text="formatMoney(viewInvoice.discount_amount)"></span>
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <span class="text-sm text-sp-text-muted">{{ __('Tax') }}</span>

                            <span class="font-semibold tabular-nums text-sp-text">
                                Rs <span x-text="formatMoney(viewInvoice.tax_amount)"></span>
                            </span>
                        </div>

                        <div class="border-t border-sp-border pt-3">
                            <div class="flex items-center justify-between gap-4">
                                <span class="text-sm font-bold text-sp-text">{{ __('Total') }}</span>

                                <span class="text-lg font-extrabold tabular-nums text-sp-primary dark:text-sp-success">
                                    Rs <span x-text="formatMoney(viewInvoice.total_amount)"></span>
                                </span>
                            </div>

                            <div class="mt-2 flex items-center justify-between gap-4">
                                <span class="text-sm font-bold text-sp-text">{{ __('Paid') }}</span>

                                <span class="text-sm font-bold tabular-nums text-sp-text">
                                    Rs <span x-text="formatMoney(paymentTotal())"></span>
                                </span>
                            </div>

                            <div class="mt-1 flex items-center justify-between gap-4">
                                <span class="text-sm font-bold text-sp-text">{{ __('Balance') }}</span>

                                <span class="text-sm font-extrabold tabular-nums text-sp-info">
                                    Rs <span x-text="formatMoney(balanceDue())"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <x-slot:footer>
        <button
            type="button"
            @click="closeView()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Close') }}
        </button>

        <a
            x-show="viewInvoice && viewInvoice.id"
            x-bind:href="'{{ url('/admin/sales') }}/' + viewInvoice.id + '/print'"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-2 rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
        >
            <x-stockpilot.icon name="file-text" class="h-4 w-4" />
            {{ __('Print receipt') }}
        </a>
    </x-slot:footer>

</x-stockpilot.modal>