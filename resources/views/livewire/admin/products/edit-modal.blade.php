<x-stockpilot.modal
    open="editProductOpen"
    close="closeEditProduct()"
    :title="__('Edit product')"
    :subtitle="__('Update product master data. Stock levels are managed separately.')"
    :eyebrow="__('Product catalog')"
    icon="products"
    heading-id="edit-product-title"
    dialog-label="{{ __('Edit product') }}"
    width="max-w-2xl"
>

    <form
        id="product-edit-form"
        method="POST"
        x-bind:action="editingProduct ? `{{ url('/admin/products') }}/${editingProduct.id}` : '#'"
    >
        @csrf
        @method('PATCH')

        <div class="max-h-[calc(100vh-12rem)] overflow-y-auto">
            <div class="space-y-5 px-6 py-6">

                <section class="rounded-2xl border border-sp-border bg-sp-surface-muted/50 p-4 sm:p-5 dark:border-sp-border dark:bg-sp-surface-soft/30">

                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-sp-text">
                            {{ __('Product information') }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-sp-text-muted">
                            {{ __('Master data is shared across inventory, purchasing, and sales.') }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label for="edit_product_name" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Product name') }}
                            </label>

                            <input
                                id="edit_product_name"
                                name="name"
                                type="text"
                                required
                                maxlength="200"
                                x-model="editingProduct.name"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                        </div>

                        <div>
                            <label for="edit_product_sku" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('SKU') }}
                            </label>

                            <input
                                id="edit_product_sku"
                                name="sku"
                                type="text"
                                required
                                maxlength="100"
                                x-model="editingProduct.sku"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm uppercase text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                        </div>

                        <div>
                            <label for="edit_product_category" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Category') }}
                            </label>

                            <select
                                id="edit_product_category"
                                name="category_id"
                                required
                                x-model="editingProduct.category_id"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                                <option value="">
                                    {{ __('Select category') }}
                                </option>

                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit_product_cost_price" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Cost price') }}
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-xs font-semibold text-sp-text-muted">
                                    Rs.
                                </span>

                                <input
                                    id="edit_product_cost_price"
                                    name="cost_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    inputmode="decimal"
                                    x-model="editingProduct.cost_price"
                                    class="block w-full rounded-xl border border-sp-border bg-sp-surface py-2.5 pe-3.5 ps-11 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="edit_product_selling_price" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Selling price') }}
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-xs font-semibold text-sp-text-muted">
                                    Rs.
                                </span>

                                <input
                                    id="edit_product_selling_price"
                                    name="selling_price"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    required
                                    inputmode="decimal"
                                    x-model="editingProduct.selling_price"
                                    class="block w-full rounded-xl border border-sp-border bg-sp-surface py-2.5 pe-3.5 ps-11 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="edit_product_reorder_level" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Reorder level') }}
                            </label>

                            <input
                                id="edit_product_reorder_level"
                                name="reorder_level"
                                type="number"
                                min="0"
                                step="0.001"
                                required
                                inputmode="decimal"
                                x-model="editingProduct.reorder_level"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="edit_product_description" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Description') }}
                            </label>

                            <textarea
                                id="edit_product_description"
                                name="description"
                                rows="4"
                                x-model="editingProduct.description"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm leading-6 text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            ></textarea>
                        </div>

                    </div>

                </section>

                <section class="rounded-2xl border border-sp-warning/30 bg-sp-warning-soft/80 p-4 sm:p-5 dark:border-sp-warning/20 dark:bg-sp-warning-soft/40">

                    <div class="flex gap-3">

                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-sp-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                        </svg>

                        <div class="text-sm leading-6 text-sp-text-muted">
                            <p class="font-semibold text-sp-text">
                                {{ __('Stock levels') }}
                            </p>

                            <p class="mt-1">
                                {{ __('Use a manual stock adjustment to change current quantity. Product status cannot be changed here.') }}
                            </p>
                        </div>

                    </div>

                </section>

            </div>
        </div>

    </form>

    <x-slot:footer>
        <button
            type="button"
            @click="closeEditProduct()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Cancel') }}
        </button>

        <button
            type="submit"
            form="product-edit-form"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-5 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
        >
            <x-stockpilot.icon name="check" class="h-4 w-4" />
            {{ __('Save changes') }}
        </button>
    </x-slot:footer>

</x-stockpilot.modal>