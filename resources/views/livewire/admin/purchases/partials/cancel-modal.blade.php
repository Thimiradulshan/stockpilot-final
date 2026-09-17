<x-stockpilot.modal
    open="cancelOpen"
    close="closeCancel()"
    :title="__('Cancel purchase?')"
    :subtitle="__('This will reverse the stock quantities associated with this purchase.')"
    :eyebrow="__('Purchasing')"
    icon="alert-triangle"
    heading-id="cancel-purchase-title"
    dialog-label="{{ __('Cancel purchase') }}"
    width="max-w-lg"
>

    <div class="space-y-4 px-6 py-6">

        <div class="rounded-2xl border border-sp-danger/30 bg-sp-danger-soft p-4">

            <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-sp-danger">
                {{ __('Purchase') }}
            </p>

            <p class="mt-1 text-base font-extrabold text-sp-danger" x-text="cancelPurchase.purchase_number || '—'"></p>

            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold text-sp-danger">{{ __('Supplier') }}</p>

                    <p class="mt-0.5 truncate text-sm font-semibold text-sp-danger" x-text="cancelPurchase.supplier?.name || '—'"></p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-sp-danger">{{ __('Current status') }}</p>

                    <p class="mt-0.5 text-sm font-semibold capitalize text-sp-danger" x-text="cancelPurchase.status || '—'"></p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-sp-danger">{{ __('Purchase date') }}</p>

                    <p class="mt-0.5 text-sm font-semibold text-sp-danger" x-text="cancelPurchase.purchase_date ? formatDate(cancelPurchase.purchase_date) : '—'"></p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-sp-danger">{{ __('Total') }}</p>

                    <p class="mt-0.5 text-sm font-bold tabular-nums text-sp-danger">
                        Rs <span x-text="formatMoney(cancelPurchase.total_amount)"></span>
                    </p>
                </div>
            </div>

        </div>

        <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">

            <div class="flex gap-3">
                <x-stockpilot.icon name="info" class="mt-0.5 h-5 w-5 shrink-0 text-sp-primary dark:text-sp-success" />

                <div class="text-sm leading-6 text-sp-text-muted">
                    <p class="font-bold text-sp-text">
                        {{ __('Inventory impact') }}
                    </p>

                    <p class="mt-1">
                        {{ __('The system will create reversing stock movements inside the same transaction. The original purchase record and stock ledger remain auditable.') }}
                    </p>
                </div>
            </div>

        </div>

        <div class="rounded-xl border border-sp-warning/30 bg-sp-warning-soft p-4">
            <p class="text-sm font-semibold leading-5 text-sp-warning">
                {{ __('Cancellation cannot be undone automatically. Confirm that you want to proceed.') }}
            </p>
        </div>

    </div>

    <x-slot:footer>
        <button
            type="button"
            @click="closeCancel()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Keep purchase') }}
        </button>

        <form
            method="POST"
            x-bind:action="cancelPurchase.id ? `{{ url('/admin/purchases') }}/${cancelPurchase.id}/cancel` : '#'"
        >
            @csrf

            <button
                type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-sp-danger px-5 py-2.5 text-sm font-bold text-white transition hover:bg-sp-danger focus:outline-none focus:ring-4 focus:ring-sp-danger/20"
            >
                <x-stockpilot.icon name="x-circle" class="h-4 w-4" />
                {{ __('Confirm cancellation') }}
            </button>
        </form>
    </x-slot:footer>

</x-stockpilot.modal>