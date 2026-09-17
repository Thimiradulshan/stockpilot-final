<x-stockpilot.modal
    open="createOpen"
    close="closeCreate()"
    :title="__('New purchase')"
    :subtitle="__('Record supplier stock received and update inventory atomically.')"
    :eyebrow="__('Purchasing')"
    icon="shopping-cart"
    heading-id="create-purchase-title"
    dialog-label="{{ __('New purchase') }}"
    width="max-w-5xl"
>

    <form
        id="purchase-create-form"
        method="POST"
        action="{{ route('admin.purchases.store') }}"
    >
        @csrf

        <div class="max-h-[calc(100vh-12rem)] overflow-y-auto">
            <div class="space-y-6 px-6 py-6">

                <section>
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-sp-text">
                            {{ __('Purchase details') }}
                        </h3>

                        <p class="mt-1 text-xs text-sp-text-muted">
                            {{ __('Enter the supplier and purchase header information.') }}
                        </p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">

                        <div class="lg:col-span-2">
                            <label for="purchase_number" class="mb-1.5 block text-sm font-semibold text-sp-text">
                                {{ __('Purchase number') }} <span class="text-sp-danger">*</span>
                            </label>

                            <input
                                id="purchase_number"
                                name="purchase_number"
                                type="text"
                                required
                                maxlength="50"
                                x-model="createForm.purchase_number"
                                placeholder="e.g. PO-2026-0001"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            />
                        </div>

                        <div class="lg:col-span-2">
                            <label for="supplier_id" class="mb-1.5 block text-sm font-semibold text-sp-text">
                                {{ __('Supplier') }} <span class="text-sp-danger">*</span>
                            </label>

                            <select
                                id="supplier_id"
                                name="supplier_id"
                                required
                                x-model="createForm.supplier_id"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                                <option value="">{{ __('Select supplier') }}</option>

                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name }}
                                        @if ($supplier->company)
                                            — {{ $supplier->company }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="purchase_date" class="mb-1.5 block text-sm font-semibold text-sp-text">
                                {{ __('Purchase date') }} <span class="text-sp-danger">*</span>
                            </label>

                            <input
                                id="purchase_date"
                                name="purchase_date"
                                type="date"
                                required
                                value="{{ now()->toDateString() }}"
                                x-model="createForm.purchase_date"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            />
                        </div>

                        <div>
                            <label for="discount_amount" class="mb-1.5 block text-sm font-semibold text-sp-text">
                                {{ __('Header discount') }}
                            </label>

                            <input
                                id="discount_amount"
                                name="discount_amount"
                                type="number"
                                min="0"
                                step="0.01"
                                value="0.00"
                                x-model="createForm.discount_amount"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            />
                        </div>

                        <div>
                            <label for="tax_amount" class="mb-1.5 block text-sm font-semibold text-sp-text">
                                {{ __('Header tax') }}
                            </label>

                            <input
                                id="tax_amount"
                                name="tax_amount"
                                type="number"
                                min="0"
                                step="0.01"
                                value="0.00"
                                x-model="createForm.tax_amount"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            />
                        </div>

                        <div class="lg:col-span-2">
                            <label for="notes" class="mb-1.5 block text-sm font-semibold text-sp-text">
                                {{ __('Notes') }}
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                rows="2"
                                maxlength="5000"
                                x-model="createForm.notes"
                                placeholder="{{ __('Optional internal notes') }}"
                                class="block w-full resize-y rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            ></textarea>
                        </div>

                    </div>
                </section>

                <section>
                    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-sp-text">
                                {{ __('Purchase items') }}
                            </h3>

                            <p class="mt-1 text-xs text-sp-text-muted">
                                {{ __('Add each product and the quantity received.') }}
                            </p>
                        </div>

                        <button
                            type="button"
                            @click="addItem()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-sp-primary/30 bg-sp-info-soft px-3 py-2 text-xs font-extrabold text-sp-info-foreground transition hover:bg-sp-info-soft focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:bg-sp-info-soft dark:text-sp-info"
                        >
                            <x-stockpilot.icon name="plus" class="h-4 w-4" />
                            {{ __('Add item') }}
                        </button>
                    </div>

                    <div class="overflow-hidden rounded-2xl border border-sp-border">

                        <div class="hidden bg-sp-surface-muted/70 px-4 py-3 text-xs font-extrabold uppercase tracking-[0.08em] text-sp-text-subtle md:grid md:grid-cols-[minmax(0,2fr)_120px_140px_120px_40px] md:gap-3 dark:bg-sp-surface-muted">
                            <div>{{ __('Product') }}</div>
                            <div>{{ __('Quantity') }}</div>
                            <div>{{ __('Unit cost') }}</div>
                            <div class="text-right">{{ __('Line total') }}</div>
                            <div></div>
                        </div>

                        <div class="divide-y divide-sp-border">
                            <template x-for="(item, index) in createForm.items" :key="item.key">
                                <div class="p-4">

                                    <div class="grid gap-3 md:grid-cols-[minmax(0,2fr)_120px_140px_120px_40px] md:items-end">

                                        <div>
                                            <label :for="`purchase_item_product_${index}`" class="mb-1.5 block text-xs font-semibold text-sp-text-muted md:hidden">
                                                {{ __('Product') }}
                                            </label>

                                            <select
                                                :id="`purchase_item_product_${index}`"
                                                :name="`items[${index}][product_id]`"
                                                required
                                                x-model="item.product_id"
                                                @change="syncItem(index)"
                                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                            >
                                                <option value="">{{ __('Select product') }}</option>

                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->name }} — {{ $product->sku }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <p
                                                x-show="item.product_id"
                                                x-text="selectedProductLabel(item.product_id)"
                                                class="mt-1.5 text-xs text-sp-text-muted"
                                            ></p>
                                        </div>

                                        <div>
                                            <label :for="`purchase_item_quantity_${index}`" class="mb-1.5 block text-xs font-semibold text-sp-text-muted md:hidden">
                                                {{ __('Quantity') }}
                                            </label>

                                            <input
                                                :id="`purchase_item_quantity_${index}`"
                                                :name="`items[${index}][quantity]`"
                                                type="number"
                                                min="0.001"
                                                step="0.001"
                                                required
                                                x-model="item.quantity"
                                                @input="syncItem(index)"
                                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                            />
                                        </div>

                                        <div>
                                            <label :for="`purchase_item_unit_cost_${index}`" class="mb-1.5 block text-xs font-semibold text-sp-text-muted md:hidden">
                                                {{ __('Unit cost') }}
                                            </label>

                                            <input
                                                :id="`purchase_item_unit_cost_${index}`"
                                                :name="`items[${index}][unit_cost]`"
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                required
                                                x-model="item.unit_cost"
                                                @input="syncItem(index)"
                                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                            />
                                        </div>

                                        <div>
                                            <label class="mb-1.5 block text-xs font-semibold text-sp-text-muted md:hidden">
                                                {{ __('Line total') }}
                                            </label>

                                            <div class="rounded-xl bg-sp-surface-muted px-3.5 py-2.5 text-right text-sm font-extrabold tabular-nums text-sp-text dark:bg-sp-surface-muted">
                                                Rs
                                                <span x-text="formatMoney(item.line_total)"></span>
                                            </div>
                                        </div>

                                        <div class="flex justify-end">
                                            <button
                                                type="button"
                                                class="rounded-lg p-2 text-sp-text-subtle transition hover:bg-sp-danger-soft hover:text-sp-danger focus:outline-none focus:ring-2 focus:ring-sp-danger/20 disabled:cursor-not-allowed disabled:opacity-40"
                                                @click="removeItem(index)"
                                                x-bind:disabled="createForm.items.length === 1"
                                                aria-label="{{ __('Remove purchase item') }}"
                                            >
                                                <x-stockpilot.icon name="trash-2" class="h-4 w-4" />
                                            </button>
                                        </div>

                                    </div>

                                </div>
                            </template>
                        </div>

                    </div>
                </section>

                <section class="rounded-2xl border border-sp-border bg-sp-surface-muted/50 p-4 sm:p-5 dark:border-sp-border dark:bg-sp-surface-soft/30">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Subtotal') }}
                            </p>

                            <p class="mt-1 text-lg font-bold tabular-nums text-sp-text">
                                Rs <span x-text="formatMoney(purchaseSubtotal())"></span>
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Adjustments') }}
                            </p>

                            <p class="mt-1 text-lg font-bold tabular-nums text-sp-text">
                                Rs <span x-text="formatMoney(purchaseAdjustmentTotal())"></span>
                            </p>
                        </div>

                        <div class="sm:text-right">
                            <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                                {{ __('Estimated total') }}
                            </p>

                            <p class="mt-1 text-2xl font-extrabold tabular-nums text-sp-primary dark:text-sp-success">
                                Rs <span x-text="formatMoney(purchaseTotal())"></span>
                            </p>
                        </div>
                    </div>

                    <p class="mt-3 text-xs text-sp-text-muted">
                        {{ __('Final totals, discounts, taxes, product status, and stock changes are validated again on the server.') }}
                    </p>
                </section>

            </div>
        </div>

    </form>

    <x-slot:footer>
        <button
            type="button"
            @click="closeCreate()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Cancel') }}
        </button>

        <button
            type="submit"
            form="purchase-create-form"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-5 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
        >
            <x-stockpilot.icon name="check" class="h-4 w-4" />
            {{ __('Save purchase') }}
        </button>
    </x-slot:footer>

</x-stockpilot.modal>