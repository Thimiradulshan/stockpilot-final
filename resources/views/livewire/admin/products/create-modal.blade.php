<x-stockpilot.modal
    open="createProductOpen"
    close="closeCreateProduct()"
    :title="__('Add product')"
    :subtitle="__('Create a product before recording your first purchase.')"
    :eyebrow="__('Product catalog')"
    icon="products"
    heading-id="create-product-title"
    dialog-label="{{ __('Add product') }}"
    width="max-w-2xl"
>

    @if ($errors->any())
        <div class="border-b border-sp-danger-soft bg-sp-danger-soft px-6 py-4">

            <div class="flex gap-3">

                <svg class="mt-0.5 h-5 w-5 shrink-0 text-sp-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="m9 9 6 6"></path>
                    <path d="m15 9-6 6"></path>
                </svg>

                <div>
                    <p class="text-sm font-bold text-sp-danger">
                        {{ __('Please correct the following errors.') }}
                    </p>

                    <ul class="mt-1 space-y-1 text-sm text-sp-danger">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

            </div>

        </div>
    @endif

    <form
        id="product-create-form"
        method="POST"
        action="{{ route('admin.products.store') }}"
    >
        @csrf

        <div class="max-h-[calc(100vh-12rem)] overflow-y-auto">
            <div class="space-y-5 px-6 py-6">

                <section class="rounded-2xl border border-sp-border bg-sp-surface-muted/50 p-4 sm:p-5 dark:border-sp-border dark:bg-sp-surface-soft/30">

                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-sp-text">
                            {{ __('Product information') }}
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-sp-text-muted">
                            {{ __('Enter the product details used throughout inventory and sales operations.') }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <label for="create_product_name" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Product name') }}
                            </label>

                            <input
                                id="create_product_name"
                                name="name"
                                type="text"
                                value="{{ old('name') }}"
                                maxlength="200"
                                required
                                autocomplete="off"
                                placeholder="e.g. Coca-Cola 500ml"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                        </div>

                        <div>
                            <label for="create_product_sku" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('SKU') }}
                            </label>

                            <input
                                id="create_product_sku"
                                name="sku"
                                type="text"
                                value="{{ old('sku') }}"
                                maxlength="100"
                                required
                                autocomplete="off"
                                placeholder="e.g. COKE-500"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm uppercase text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                        </div>

                        <div>
                            <label for="create_product_category" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Category') }}
                            </label>

                            <select
                                id="create_product_category"
                                name="category_id"
                                required
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                                <option value="">
                                    {{ __('Select category') }}
                                </option>

                                @foreach ($categories as $category)
                                    <option
                                        value="{{ $category->id }}"
                                        @selected((string) old('category_id') === (string) $category->id)
                                    >
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($categories->isEmpty())
                                <p class="mt-2 text-xs font-semibold text-sp-warning">
                                    {{ __('No active categories are available. Create an active category first.') }}
                                </p>
                            @endif
                        </div>

                        <div>
                            <label for="create_product_cost_price" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Cost price') }}
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-xs font-semibold text-sp-text-muted">
                                    Rs.
                                </span>

                                <input
                                    id="create_product_cost_price"
                                    name="cost_price"
                                    type="number"
                                    value="{{ old('cost_price') }}"
                                    min="0"
                                    step="0.01"
                                    required
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    class="block w-full rounded-xl border border-sp-border bg-sp-surface py-2.5 pe-3.5 ps-11 text-sm tabular-nums text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="create_product_selling_price" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Selling price') }}
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5 text-xs font-semibold text-sp-text-muted">
                                    Rs.
                                </span>

                                <input
                                    id="create_product_selling_price"
                                    name="selling_price"
                                    type="number"
                                    value="{{ old('selling_price') }}"
                                    min="0"
                                    step="0.01"
                                    required
                                    inputmode="decimal"
                                    placeholder="0.00"
                                    class="block w-full rounded-xl border border-sp-border bg-sp-surface py-2.5 pe-3.5 ps-11 text-sm tabular-nums text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="create_product_reorder_level" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Reorder level') }}
                            </label>

                            <input
                                id="create_product_reorder_level"
                                name="reorder_level"
                                type="number"
                                value="{{ old('reorder_level', '0') }}"
                                min="0"
                                step="0.001"
                                required
                                inputmode="decimal"
                                placeholder="0.000"
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm tabular-nums text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >
                        </div>

                        <div class="sm:col-span-2">
                            <label for="create_product_description" class="mb-1.5 block text-xs font-semibold text-sp-text-muted">
                                {{ __('Description') }}
                            </label>

                            <textarea
                                id="create_product_description"
                                name="description"
                                rows="4"
                                placeholder="Optional product description..."
                                class="block w-full rounded-xl border border-sp-border bg-sp-surface px-3.5 py-2.5 text-sm leading-6 text-sp-text outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                            >{{ old('description') }}</textarea>
                        </div>

                    </div>

                </section>

                <section class="rounded-2xl border border-sp-info/20 bg-sp-info-soft/80 p-4 sm:p-5 dark:border-sp-info/30 dark:bg-sp-info-soft/40">

                    <div class="flex gap-3">

                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-sp-info" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <circle cx="12" cy="12" r="9"></circle>
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                        </svg>

                        <div class="text-sm leading-6 text-sp-text-muted">
                            <p class="font-semibold text-sp-text">
                                {{ __('Initial stock') }}
                            </p>

                            <p class="mt-1">
                                {{ __('New products start with zero stock. Use a purchase or stock adjustment to add inventory.') }}
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
            @click="closeCreateProduct()"
            class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
        >
            {{ __('Cancel') }}
        </button>

        <button
            type="submit"
            form="product-create-form"
            @disabled($categories->isEmpty())
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-sp-primary px-5 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
        >
            <x-stockpilot.icon name="plus" class="h-4 w-4" />
            {{ __('Save product') }}
        </button>
    </x-slot:footer>

</x-stockpilot.modal>