<x-stockpilot.modal
    open="stockAdjustOpen"
    close="closeStockAdjust()"
    :title="__('Adjust stock')"
    :subtitle="__('Apply a manual stock movement with a signed quantity.')"
    :eyebrow="__('Product catalog')"
    icon="shopping-cart"
    heading-id="stock-adjust-title"
    dialog-label="{{ __('Adjust stock') }}"
    width="max-w-lg"
>

    <form
        id="product-stock-adjust-form"
        method="POST"
        x-bind:action="editingProduct ? `{{ url('/admin/products') }}/${editingProduct.id}/stock` : '#'"
        class="space-y-5 px-6 py-6"
    >
        @csrf
        @method('PATCH')

        <input type="hidden" name="signed_quantity" x-bind:value="stockAdjustDirection === 'remove' ? -Math.abs(Number(stockAdjustQuantity)) : Math.abs(Number(stockAdjustQuantity))">

        <div class="rounded-2xl border border-sp-border bg-sp-surface-muted/60 p-4 dark:border-sp-border dark:bg-sp-surface-soft/30">

            <div class="flex items-center gap-3">

                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">
                    <x-stockpilot.icon name="products" class="h-5 w-5" />
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-sp-text" x-text="editingProduct?.name ?? '—'"></p>

                    <p class="mt-0.5 truncate text-xs text-sp-text-subtle" x-text="editingProduct ? 'SKU: ' + editingProduct.sku : ''"></p>
                </div>

            </div>

        </div>

        <div class="grid grid-cols-2 gap-3">

            <button
                type="button"
                @click="stockAdjustDirection = 'receive'"
                @class([
                    'inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-extrabold shadow-sm transition',
                    'border-sp-success/40 bg-sp-success-soft text-sp-success dark:bg-sp-success-soft dark:text-sp-success' => true,
                ])
                x-bind:class="stockAdjustDirection === 'receive'
                    ? 'ring-2 ring-sp-success/40 border-sp-success'
                    : 'border-sp-border-strong bg-sp-surface text-sp-text-muted dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text-muted'"
            >
                <x-stockpilot.icon name="plus" class="h-4 w-4" />
                {{ __('Add stock') }}
            </button>

            <button
                type="button"
                @click="stockAdjustDirection = 'remove'"
                x-bind:class="stockAdjustDirection === 'remove'
                    ? 'ring-2 ring-sp-danger/40 border-sp-danger'
                    : 'border-sp-border-strong bg-sp-surface text-sp-text-muted dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text-muted'"
                class="inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-extrabold shadow-sm transition"
            >
                <x-stockpilot.icon name="x-circle" class="h-4 w-4" />
                {{ __('Remove stock') }}
            </button>

        </div>

        <div>
            <label for="stock-adjust-quantity" class="mb-2 block text-sm font-semibold text-sp-text">
                {{ __('Quantity') }}
            </label>

            <input
                id="stock-adjust-quantity"
                type="number"
                min="0"
                step="0.001"
                required
                inputmode="decimal"
                x-model="stockAdjustQuantity"
                placeholder="0.000"
                class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
        </div>

        <div>
            <label for="stock-adjust-reason" class="mb-2 block text-sm font-semibold text-sp-text">
                {{ __('Reason') }}
            </label>

            <input
                id="stock-adjust-reason"
                name="reason"
                type="text"
                maxlength="255"
                required
                placeholder="e.g. Damaged goods, recount correction"
                class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
        </div>

        <div>
            <label for="stock-adjust-notes" class="mb-2 block text-sm font-semibold text-sp-text">
                {{ __('Notes') }}
            </label>

            <textarea
                id="stock-adjust-notes"
                name="notes"
                rows="3"
                maxlength="5000"
                placeholder="Optional extra context..."
                class="w-full resize-none rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            ></textarea>
        </div>

    </form>

    <x-slot:footer>
        <button
            type="button"
            @click="closeStockAdjust()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Cancel') }}
        </button>

        <button
            type="submit"
            form="product-stock-adjust-form"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-5 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
        >
            <x-stockpilot.icon name="check" class="h-4 w-4" />
            {{ __('Apply adjustment') }}
        </button>
    </x-slot:footer>

</x-stockpilot.modal>