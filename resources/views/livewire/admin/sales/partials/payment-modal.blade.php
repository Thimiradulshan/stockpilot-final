<x-stockpilot.modal
    open="paymentOpen"
    close="closePayment()"
    :title="__('Record payment')"
    :subtitle="__('Apply a payment against this invoice.')"
    :eyebrow="__('Sales')"
    icon="credit-card"
    heading-id="record-payment-title"
    dialog-label="{{ __('Record payment') }}"
    width="max-w-xl"
>

    <div class="space-y-4 px-6 py-6">

        <div class="rounded-2xl border border-sp-border bg-sp-surface-muted/50 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Invoice') }}
                    </p>

                    <p class="mt-1 text-sm font-extrabold text-sp-text" x-text="paymentInvoice.invoice_number || '—'"></p>

                    <p class="mt-0.5 truncate text-xs text-sp-text-subtle" x-text="paymentInvoice.customer?.name || ''"></p>
                </div>

                <div class="sm:text-right">
                    <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-sp-text-subtle">
                        {{ __('Balance due') }}
                    </p>

                    <p class="mt-1 text-lg font-extrabold tabular-nums text-sp-info">
                        Rs <span x-text="formatMoney(paymentBalance())"></span>
                    </p>
                </div>
            </div>

        </div>

        <form
            id="payment-create-form"
            method="POST"
            x-bind:action="paymentInvoice.id ? `{{ url('/admin/sales') }}/${paymentInvoice.id}/payments` : '#'"
        >
            @csrf

            <input type="hidden" name="idempotency_key" x-bind:value="paymentIdempotencyKey" />

            <div class="grid gap-4 sm:grid-cols-2">

                <div>
                    <label for="payment_amount" class="mb-1.5 block text-sm font-semibold text-sp-text">
                        {{ __('Amount') }} <span class="text-sp-danger">*</span>
                    </label>

                    <input
                        id="payment_amount"
                        name="amount"
                        type="number"
                        min="0.01"
                        step="0.01"
                        required
                        x-model="paymentForm.amount"
                        :placeholder="'0.00'"
                        class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    />
                </div>

                <div>
                    <label for="payment_method" class="mb-1.5 block text-sm font-semibold text-sp-text">
                        {{ __('Payment method') }} <span class="text-sp-danger">*</span>
                    </label>

                    <select
                        id="payment_method"
                        name="payment_method"
                        required
                        x-model="paymentForm.payment_method"
                        class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                        <option value="">{{ __('Select method') }}</option>
                        <option value="cash">{{ __('Cash') }}</option>
                        <option value="card">{{ __('Card') }}</option>
                        <option value="bank_transfer">{{ __('Bank transfer') }}</option>
                        <option value="credit">{{ __('Credit') }}</option>
                    </select>
                </div>

            </div>
        </form>

    </div>

    <x-slot:footer>
        <button
            type="button"
            @click="closePayment()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Cancel') }}
        </button>

        <button
            type="submit"
            form="payment-create-form"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-5 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
        >
            <x-stockpilot.icon name="check" class="h-4 w-4" />
            {{ __('Record payment') }}
        </button>
    </x-slot:footer>

</x-stockpilot.modal>