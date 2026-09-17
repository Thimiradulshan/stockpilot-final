<x-stockpilot.admin-page
    x-data="{
        createOpen: false,
        editOpen: false,
        editingSupplier: null,

        openCreate() {
            this.editOpen = false;
            this.editingSupplier = null;
            this.createOpen = true;
        },

        openEdit(supplier) {
            this.createOpen = false;
            this.editingSupplier = supplier;
            this.editOpen = true;
        },

        closeModals() {
            this.createOpen = false;
            this.editOpen = false;
            this.editingSupplier = null;
        },
    }"
    escapeAction="closeModals()"
>

    <x-stockpilot.page-header
        :section-label="__('Master data')"
        :section-current="__('Suppliers')"
        :title="__('Suppliers')"
        :subtitle="__('Supplier catalog')"
        :description="__('Manage supplier contacts and purchasing relationships.')"
        :tags="[
            ['label' => __('Catalog'), 'tone' => 'primary'],
            ['label' => __('Active'), 'tone' => 'success'],
        ]"
    >
        <x-slot:actions>
            @can('create', \App\Models\Supplier::class)
                <x-stockpilot.primary-action
                    :label="__('Add supplier')"
                    icon="suppliers"
                    click="openCreate()"
                />
            @endcan
        </x-slot:actions>
    </x-stockpilot.page-header>

    <x-stockpilot.master-data-tabs />

    <x-stockpilot.toast-alerts />

    <section aria-label="{{ __('Supplier summary') }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            <x-stockpilot.kpi-card
                :label="__('Total suppliers')"
                :value="number_format($totalSuppliers)"
                :hint="__('Entire supplier catalog')"
                icon="suppliers"
                tone="primary"
                action="clearFilters()"
                :active="! $this->hasActiveFilters()"
            />

            <x-stockpilot.kpi-card
                :label="__('Active suppliers')"
                :value="number_format($activeSuppliers)"
                :hint="__('Available for purchasing')"
                icon="check"
                tone="success"
                action="$set('status', 'active')"
                :active="$status === 'active'"
            />

            <x-stockpilot.kpi-card
                :label="__('Inactive suppliers')"
                :value="number_format($inactiveSuppliers)"
                :hint="__('Retained for history')"
                icon="x-circle"
                tone="info"
                action="$set('status', 'inactive')"
                :active="$status === 'inactive'"
            />

        </div>
    </section>

    <x-stockpilot.filter-bar aria-label="{{ __('Supplier filters') }}">

        <div class="w-full lg:max-w-2xl">
            <x-stockpilot.search-input
                id="supplier-search"
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search by name, company, phone or email...')"
            />
        </div>

        <div class="flex flex-wrap items-center gap-2">

            <select
                wire:model.live="status"
                class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text shadow-sm outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
                <option value="">{{ __('All statuses') }}</option>
                <option value="active">{{ __('Active') }}</option>
                <option value="inactive">{{ __('Inactive') }}</option>
            </select>

            @if ($this->hasActiveFilters())
                <button
                    type="button"
                    wire:click="clearFilters"
                    class="rounded-xl border border-sp-info/30 bg-sp-info-soft px-4 py-3.5 text-sm font-extrabold text-sp-info-foreground shadow-sm transition hover:bg-sp-info-soft dark:bg-sp-info-soft dark:text-sp-info"
                >
                    {{ __('Clear filters') }}
                </button>
            @endif

        </div>

    </x-stockpilot.filter-bar>

    <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Supplier catalog') }}">

        <table class="min-w-full border-collapse">

            <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">

                <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Supplier') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Contact') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Products') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Purchases') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Status') }}
                    </th>

                    <th scope="col" class="w-[180px] px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Actions') }}
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                @forelse ($suppliers as $supplier)

                    <tr
                        wire:key="supplier-row-{{ $supplier->id }}"
                        class="group transition duration-150 odd:bg-sp-surface even:bg-sp-surface-muted/40 hover:bg-sp-primary/[0.04] dark:odd:bg-sp-surface dark:even:bg-sp-surface-muted dark:hover:bg-sp-brand-dark/70"
                    >

                        <td class="px-6 py-5">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary shadow-sm dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

                                    <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-sp-accent/25"></span>

                                    <span class="relative text-sm font-extrabold">
                                        {{ strtoupper(mb_substr($supplier->name, 0, 1)) }}
                                    </span>

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-[15px] font-extrabold text-sp-text">
                                        {{ $supplier->name }}
                                    </p>

                                    @if ($supplier->company)
                                        <p class="mt-1 truncate text-xs font-semibold text-sp-text-subtle">
                                            {{ $supplier->company }}
                                        </p>
                                    @endif

                                </div>

                            </div>

                        </td>

                        <td class="max-w-[320px] px-6 py-5">

                            @if ($supplier->phone || $supplier->email)

                                <div class="space-y-1">

                                    @if ($supplier->phone)
                                        <p class="text-sm font-semibold text-sp-text">
                                            {{ $supplier->phone }}
                                        </p>
                                    @endif

                                    @if ($supplier->email)
                                        <p class="truncate text-sm text-sp-text-muted">
                                            {{ $supplier->email }}
                                        </p>
                                    @endif

                                </div>

                            @else

                                <p class="text-sm text-sp-text-subtle">
                                    {{ __('No contact details') }}
                                </p>

                            @endif

                        </td>

                        <td class="px-6 py-5 text-center">

                            <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-sp-primary/10 px-3 py-2 text-sm font-extrabold text-sp-primary shadow-sm dark:bg-sp-primary/10 dark:text-sp-success">
                                {{ number_format($supplier->products_count) }}
                            </span>

                        </td>

                        <td class="px-6 py-5 text-center">

                            <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-sp-info-soft px-3 py-2 text-sm font-extrabold text-sp-info-foreground shadow-sm dark:bg-sp-info-soft dark:text-sp-info">
                                {{ number_format($supplier->purchases_count) }}
                            </span>

                        </td>

                        <td class="px-6 py-5 text-center">

                            @if ($supplier->status === 'active')
                                <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                            @else
                                <x-stockpilot.status-badge :label="__('Inactive')" tone="info" />
                            @endif

                        </td>

                        <td class="px-6 py-5">

                            @can('update', $supplier)

                                <button
                                    type="button"
                                    @click="openEdit(@js([
                                        'id' => $supplier->id,
                                        'name' => $supplier->name,
                                        'company' => $supplier->company,
                                        'phone' => $supplier->phone,
                                        'email' => $supplier->email,
                                        'address' => $supplier->address,
                                        'status' => $supplier->status,
                                    ]))"
                                    class="inline-flex items-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-3.5 py-2.5 text-sm font-extrabold text-sp-text shadow-sm transition hover:border-sp-primary/30 hover:bg-sp-primary/10 hover:text-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:border-sp-border-strong dark:bg-sp-surface-muted dark:text-sp-text dark:hover:bg-sp-surface-muted dark:hover:text-sp-success"
                                >
                                    <x-stockpilot.icon name="edit" class="h-4 w-4" />
                                    {{ __('Edit') }}
                                </button>

                            @endcan

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">

                            <x-stockpilot.empty-state
                                icon="suppliers"
                                :title="$this->hasActiveFilters() ? __('No matching suppliers') : __('No suppliers yet')"
                                :message="$this->hasActiveFilters() ? __('Try changing your search or status filter.') : __('Create your first supplier to manage your purchasing relationships.')"
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

                                @can('create', \App\Models\Supplier::class)
                                    @if (! $this->hasActiveFilters())
                                        <button
                                            type="button"
                                            @click="openCreate()"
                                            class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-md transition hover:bg-sp-primary-hover"
                                        >
                                            {{ __('Add supplier') }}
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

    <div class="space-y-3 lg:hidden">

        @forelse ($suppliers as $supplier)

            <article class="rounded-2xl border border-sp-border bg-sp-surface p-4 shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <div class="flex items-start justify-between gap-3">

                    <div class="flex min-w-0 items-center gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sm font-bold text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">
                            {{ strtoupper(mb_substr($supplier->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0">

                            <h3 class="truncate text-sm font-bold text-sp-text">
                                {{ $supplier->name }}
                            </h3>

                            @if ($supplier->company)
                                <p class="mt-0.5 truncate text-xs text-sp-text-subtle">
                                    {{ $supplier->company }}
                                </p>
                            @endif

                        </div>

                    </div>

                    @if ($supplier->status === 'active')
                        <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                    @else
                        <x-stockpilot.status-badge :label="__('Inactive')" tone="info" />
                    @endif

                </div>

                @if ($supplier->phone || $supplier->email || $supplier->address)

                    <div class="mt-4 rounded-xl bg-sp-surface-muted p-3 dark:bg-sp-surface-muted">

                        @if ($supplier->phone)
                            <p class="text-sm font-semibold text-sp-text">
                                {{ $supplier->phone }}
                            </p>
                        @endif

                        @if ($supplier->email)
                            <p class="mt-1 break-all text-sm text-sp-text-muted">
                                {{ $supplier->email }}
                            </p>
                        @endif

                        @if ($supplier->address)
                            <p class="mt-2 text-sm leading-6 text-sp-text-muted">
                                {{ $supplier->address }}
                            </p>
                        @endif

                    </div>

                @endif

                <div class="mt-4 grid grid-cols-2 gap-3">

                    <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Products') }}
                        </p>

                        <p class="mt-1 text-lg font-bold text-sp-text">
                            {{ number_format($supplier->products_count) }}
                        </p>
                    </div>

                    <div class="rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">
                        <p class="text-xs font-bold uppercase tracking-[0.08em] text-sp-text-subtle">
                            {{ __('Purchases') }}
                        </p>

                        <p class="mt-1 text-lg font-bold text-sp-text">
                            {{ number_format($supplier->purchases_count) }}
                        </p>
                    </div>

                </div>

                @can('update', $supplier)
                    <button
                        type="button"
                        @click="openEdit(@js([
                            'id' => $supplier->id,
                            'name' => $supplier->name,
                            'company' => $supplier->company,
                            'phone' => $supplier->phone,
                            'email' => $supplier->email,
                            'address' => $supplier->address,
                            'status' => $supplier->status,
                        ]))"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
                    >
                        <x-stockpilot.icon name="edit" class="h-4 w-4" />
                        {{ __('Edit supplier') }}
                    </button>
                @endcan

            </article>

        @empty

            <div class="rounded-2xl border border-sp-border bg-sp-surface px-6 py-14 text-center shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <x-stockpilot.empty-state
                    icon="suppliers"
                    :title="__('No suppliers found')"
                    :message="__('Try changing your search or status filter.')"
                />

            </div>

        @endforelse

        @if ($suppliers->hasPages())
            <div>
                {{ $suppliers->links() }}
            </div>
        @endif

    </div>

    <x-stockpilot.pagination-bar
        :paginator="$suppliers"
        :item-label="__('suppliers')"
    />

    @can('create', \App\Models\Supplier::class)
        <x-stockpilot.modal
            open="createOpen"
            close="closeModals()"
            :title="__('Add supplier')"
            :subtitle="__('Create a supplier master record.')"
            :eyebrow="__('Master data')"
            icon="suppliers"
            heading-id="create-supplier-title"
            dialog-label="{{ __('Add supplier') }}"
            width="max-w-2xl"
        >
            <form
                id="supplier-create-form"
                method="POST"
                action="{{ route('admin.suppliers.store') }}"
                class="space-y-5 px-6 py-6"
            >
                @csrf

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label for="supplier-create-name" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Supplier name') }}
                        </label>

                        <input
                            id="supplier-create-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            maxlength="150"
                            required
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="supplier-create-company" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Company') }}
                        </label>

                        <input
                            id="supplier-create-company"
                            name="company"
                            type="text"
                            value="{{ old('company') }}"
                            maxlength="150"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="supplier-create-phone" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Phone') }}
                        </label>

                        <input
                            id="supplier-create-phone"
                            name="phone"
                            type="text"
                            value="{{ old('phone') }}"
                            maxlength="30"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="supplier-create-email" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Email') }}
                        </label>

                        <input
                            id="supplier-create-email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            maxlength="150"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div class="md:col-span-2">
                        <label for="supplier-create-address" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Address') }}
                        </label>

                        <textarea
                            id="supplier-create-address"
                            name="address"
                            rows="3"
                            maxlength="5000"
                            class="w-full resize-none rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >{{ old('address') }}</textarea>
                    </div>

                    <div>
                        <label for="supplier-create-status" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Status') }}
                        </label>

                        <select
                            id="supplier-create-status"
                            name="status"
                            required
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                            <option value="active" @selected(old('status', 'active') === 'active')>
                                {{ __('Active') }}
                            </option>

                            <option value="inactive" @selected(old('status') === 'inactive')>
                                {{ __('Inactive') }}
                            </option>
                        </select>
                    </div>

                </div>
            </form>

            <x-slot:footer>
                <button
                    type="button"
                    @click="closeModals()"
                    class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
                >
                    {{ __('Cancel') }}
                </button>

                <button
                    type="submit"
                    form="supplier-create-form"
                    class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
                >
                    {{ __('Create supplier') }}
                </button>
            </x-slot:footer>
        </x-stockpilot.modal>
    @endcan

    <x-stockpilot.modal
        open="editOpen"
        close="closeModals()"
        :title="__('Edit supplier')"
        :subtitle="__('Update supplier master data.')"
        :eyebrow="__('Master data')"
        icon="suppliers"
        heading-id="edit-supplier-title"
        dialog-label="{{ __('Edit supplier') }}"
        width="max-w-2xl"
    >
        <form
            id="supplier-edit-form"
            method="POST"
            x-bind:action="editingSupplier ? `{{ url('/admin/suppliers') }}/${editingSupplier.id}` : '#'"
            class="space-y-5 px-6 py-6"
        >
            @csrf
            @method('PATCH')

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label for="supplier-edit-name" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Supplier name') }}
                    </label>

                    <input
                        id="supplier-edit-name"
                        name="name"
                        type="text"
                        required
                        maxlength="150"
                        x-model="editingSupplier.name"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div>
                    <label for="supplier-edit-company" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Company') }}
                    </label>

                    <input
                        id="supplier-edit-company"
                        name="company"
                        type="text"
                        maxlength="150"
                        x-model="editingSupplier.company"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div>
                    <label for="supplier-edit-phone" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Phone') }}
                    </label>

                    <input
                        id="supplier-edit-phone"
                        name="phone"
                        type="text"
                        maxlength="30"
                        x-model="editingSupplier.phone"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div>
                    <label for="supplier-edit-email" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Email') }}
                    </label>

                    <input
                        id="supplier-edit-email"
                        name="email"
                        type="email"
                        maxlength="150"
                        x-model="editingSupplier.email"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div class="md:col-span-2">
                    <label for="supplier-edit-address" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Address') }}
                    </label>

                    <textarea
                        id="supplier-edit-address"
                        name="address"
                        rows="3"
                        maxlength="5000"
                        x-model="editingSupplier.address"
                        class="w-full resize-none rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    ></textarea>
                </div>

                <div>
                    <label for="supplier-edit-status" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Status') }}
                    </label>

                    <select
                        id="supplier-edit-status"
                        name="status"
                        required
                        x-model="editingSupplier.status"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>

            </div>
        </form>

        <x-slot:footer>
            <button
                type="button"
                @click="closeModals()"
                class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-bold text-sp-text-muted transition hover:bg-sp-surface-muted dark:border-sp-border-strong dark:bg-sp-surface dark:text-sp-text-muted"
            >
                {{ __('Cancel') }}
            </button>

            <button
                type="submit"
                form="supplier-edit-form"
                class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
            >
                {{ __('Save changes') }}
            </button>
        </x-slot:footer>
    </x-stockpilot.modal>

</x-stockpilot.admin-page>