<x-stockpilot.admin-page
    x-data="{
        createOpen: false,
        editOpen: false,
        editingCustomer: null,

        openCreate() {
            this.editOpen = false;
            this.editingCustomer = null;
            this.createOpen = true;
        },

        openEdit(customer) {
            this.createOpen = false;
            this.editingCustomer = customer;
            this.editOpen = true;
        },

        closeModals() {
            this.createOpen = false;
            this.editOpen = false;
            this.editingCustomer = null;
        },
    }"
    escapeAction="closeModals()"
>

    <x-stockpilot.page-header
        :section-label="__('Master data')"
        :section-current="__('Customers')"
        :title="__('Customers')"
        :subtitle="__('Customer catalog')"
        :description="__('Manage customer records used by StockPilot sales and invoices.')"
        :tags="[
            ['label' => __('Catalog'), 'tone' => 'primary'],
            ['label' => __('Active'), 'tone' => 'success'],
        ]"
    >
        <x-slot:actions>
            @can('create', \App\Models\Customer::class)
                <x-stockpilot.primary-action
                    :label="__('Add customer')"
                    icon="customers"
                    click="openCreate()"
                />
            @endcan
        </x-slot:actions>
    </x-stockpilot.page-header>

    <x-stockpilot.master-data-tabs />

    <x-stockpilot.toast-alerts />

    <section aria-label="{{ __('Customer summary') }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            <x-stockpilot.kpi-card
                :label="__('Total customers')"
                :value="number_format($totalCustomers)"
                :hint="__('Entire customer catalog')"
                icon="customers"
                tone="primary"
                action="clearFilters()"
                :active="! $this->hasActiveFilters()"
            />

            <x-stockpilot.kpi-card
                :label="__('Active customers')"
                :value="number_format($activeCustomers)"
                :hint="__('Available for sales')"
                icon="check"
                tone="success"
                action="$set('status', 'active')"
                :active="$status === 'active'"
            />

            <x-stockpilot.kpi-card
                :label="__('Inactive customers')"
                :value="number_format($inactiveCustomers)"
                :hint="__('Retained for history')"
                icon="x-circle"
                tone="info"
                action="$set('status', 'inactive')"
                :active="$status === 'inactive'"
            />

        </div>
    </section>

    <x-stockpilot.filter-bar aria-label="{{ __('Customer filters') }}">

        <div class="w-full lg:max-w-2xl">
            <x-stockpilot.search-input
                id="customer-search"
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search by name, phone, email or address...')"
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

    <x-stockpilot.page-table mobile="lg" aria-label="{{ __('Customer catalog') }}">

        <table class="min-w-full border-collapse">

            <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">

                <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Customer') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Phone') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Email') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Invoices') }}
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

                @forelse ($customers as $customer)

                    <tr
                        wire:key="customer-row-{{ $customer->id }}"
                        class="group transition duration-150 odd:bg-sp-surface even:bg-sp-surface-muted/40 hover:bg-sp-primary/[0.04] dark:odd:bg-sp-surface dark:even:bg-sp-surface-muted dark:hover:bg-sp-brand-dark/70"
                    >

                        <td class="px-6 py-5">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary shadow-sm dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

                                    <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-sp-accent/25"></span>

                                    <span class="relative text-sm font-extrabold">
                                        {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                                    </span>

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-[15px] font-extrabold text-sp-text">
                                        {{ $customer->name }}
                                    </p>

                                    @if ($customer->address)
                                        <p class="mt-1 max-w-xs truncate text-xs font-semibold text-sp-text-subtle">
                                            {{ $customer->address }}
                                        </p>
                                    @endif

                                </div>

                            </div>

                        </td>

                        <td class="px-6 py-5">

                            <p class="text-sm font-semibold text-sp-text">
                                {{ $customer->phone ?: '—' }}
                            </p>

                        </td>

                        <td class="px-6 py-5">

                            <p class="truncate text-sm text-sp-text-muted">
                                {{ $customer->email ?: '—' }}
                            </p>

                        </td>

                        <td class="px-6 py-5 text-center">

                            <span class="inline-flex min-w-12 items-center justify-center rounded-xl bg-sp-primary/10 px-3 py-2 text-sm font-extrabold text-sp-primary shadow-sm dark:bg-sp-primary/10 dark:text-sp-success">
                                {{ number_format($customer->invoices_count) }}
                            </span>

                        </td>

                        <td class="px-6 py-5 text-center">

                            @if ($customer->status === 'active')
                                <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                            @else
                                <x-stockpilot.status-badge :label="__('Inactive')" tone="info" />
                            @endif

                        </td>

                        <td class="px-6 py-5">

                            @can('update', $customer)

                                <button
                                    type="button"
                                    @click="openEdit(@js([
                                        'id' => $customer->id,
                                        'name' => $customer->name,
                                        'phone' => $customer->phone,
                                        'email' => $customer->email,
                                        'address' => $customer->address,
                                        'status' => $customer->status,
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
                                icon="customers"
                                :title="$this->hasActiveFilters() ? __('No matching customers') : __('No customers yet')"
                                :message="$this->hasActiveFilters() ? __('Try changing your search or status filter.') : __('Create your first customer to manage sales and invoices.')"
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

                                @can('create', \App\Models\Customer::class)
                                    @if (! $this->hasActiveFilters())
                                        <button
                                            type="button"
                                            @click="openCreate()"
                                            class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-md transition hover:bg-sp-primary-hover"
                                        >
                                            {{ __('Add customer') }}
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

        @forelse ($customers as $customer)

            <article class="rounded-2xl border border-sp-border bg-sp-surface p-4 shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <div class="flex items-start justify-between gap-3">

                    <div class="flex min-w-0 items-center gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sm font-bold text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">
                            {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                        </div>

                        <div class="min-w-0">

                            <h3 class="truncate text-sm font-bold text-sp-text">
                                {{ $customer->name }}
                            </h3>

                            @if ($customer->phone)
                                <p class="mt-0.5 text-xs text-sp-text-subtle">
                                    {{ $customer->phone }}
                                </p>
                            @endif

                        </div>

                    </div>

                    @if ($customer->status === 'active')
                        <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                    @else
                        <x-stockpilot.status-badge :label="__('Inactive')" tone="info" />
                    @endif

                </div>

                <div class="mt-4 space-y-2 text-sm">

                    <div class="flex justify-between gap-4">
                        <span class="text-sp-text-subtle">{{ __('Email') }}</span>

                        <span class="truncate text-right font-semibold text-sp-text">
                            {{ $customer->email ?: '—' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-sp-text-subtle">{{ __('Invoices') }}</span>

                        <span class="font-extrabold text-sp-primary dark:text-sp-success">
                            {{ number_format($customer->invoices_count) }}
                        </span>
                    </div>

                    @if ($customer->address)
                        <p class="rounded-xl bg-sp-surface-muted p-3 leading-6 text-sp-text-muted dark:bg-sp-surface-muted">
                            {{ $customer->address }}
                        </p>
                    @endif

                </div>

                @can('update', $customer)
                    <button
                        type="button"
                        @click="openEdit(@js([
                            'id' => $customer->id,
                            'name' => $customer->name,
                            'phone' => $customer->phone,
                            'email' => $customer->email,
                            'address' => $customer->address,
                            'status' => $customer->status,
                        ]))"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
                    >
                        <x-stockpilot.icon name="edit" class="h-4 w-4" />
                        {{ __('Edit customer') }}
                    </button>
                @endcan

            </article>

        @empty

            <div class="rounded-2xl border border-sp-border bg-sp-surface px-6 py-14 text-center shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <x-stockpilot.empty-state
                    icon="customers"
                    :title="__('No customers found')"
                    :message="__('Try changing your search or status filter.')"
                />

            </div>

        @endforelse

        @if ($customers->hasPages())
            <div>
                {{ $customers->links() }}
            </div>
        @endif

    </div>

    <x-stockpilot.pagination-bar
        :paginator="$customers"
        :item-label="__('customers')"
    />

    @can('create', \App\Models\Customer::class)
        <x-stockpilot.modal
            open="createOpen"
            close="closeModals()"
            :title="__('Add customer')"
            :subtitle="__('Add a customer record to StockPilot.')"
            :eyebrow="__('Master data')"
            icon="customers"
            heading-id="create-customer-title"
            dialog-label="{{ __('Add customer') }}"
            width="max-w-2xl"
        >
            <form
                id="customer-create-form"
                method="POST"
                action="{{ route('admin.customers.store') }}"
                class="space-y-5 px-6 py-6"
            >
                @csrf

                <div>
                    <label for="customer-create-name" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Customer name') }}
                    </label>

                    <input
                        id="customer-create-name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        required
                        maxlength="150"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label for="customer-create-phone" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Phone') }}
                        </label>

                        <input
                            id="customer-create-phone"
                            name="phone"
                            type="text"
                            value="{{ old('phone') }}"
                            maxlength="30"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="customer-create-email" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Email') }}
                        </label>

                        <input
                            id="customer-create-email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            maxlength="150"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                </div>

                <div>
                    <label for="customer-create-address" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Address') }}
                    </label>

                    <textarea
                        id="customer-create-address"
                        name="address"
                        rows="4"
                        maxlength="5000"
                        class="w-full resize-none rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >{{ old('address') }}</textarea>
                </div>

                <div>
                    <label for="customer-create-status" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Status') }}
                    </label>

                    <select
                        id="customer-create-status"
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
                    form="customer-create-form"
                    class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
                >
                    {{ __('Create customer') }}
                </button>
            </x-slot:footer>
        </x-stockpilot.modal>
    @endcan

    <x-stockpilot.modal
        open="editOpen"
        close="closeModals()"
        :title="__('Edit customer')"
        :subtitle="__('Update customer information and status.')"
        :eyebrow="__('Master data')"
        icon="customers"
        heading-id="edit-customer-title"
        dialog-label="{{ __('Edit customer') }}"
        width="max-w-2xl"
    >
        <form
            id="customer-edit-form"
            method="POST"
            x-bind:action="editingCustomer ? `{{ url('/admin/customers') }}/${editingCustomer.id}` : '#'"
            class="space-y-5 px-6 py-6"
        >
            @csrf
            @method('PATCH')

            <div>
                <label for="customer-edit-name" class="mb-2 block text-sm font-semibold text-sp-text">
                    {{ __('Customer name') }}
                </label>

                <input
                    id="customer-edit-name"
                    name="name"
                    type="text"
                    required
                    maxlength="150"
                    x-model="editingCustomer.name"
                    class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                >
            </div>

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label for="customer-edit-phone" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Phone') }}
                    </label>

                    <input
                        id="customer-edit-phone"
                        name="phone"
                        type="text"
                        maxlength="30"
                        x-model="editingCustomer.phone"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div>
                    <label for="customer-edit-email" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Email') }}
                    </label>

                    <input
                        id="customer-edit-email"
                        name="email"
                        type="email"
                        maxlength="150"
                        x-model="editingCustomer.email"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

            </div>

            <div>
                <label for="customer-edit-address" class="mb-2 block text-sm font-semibold text-sp-text">
                    {{ __('Address') }}
                </label>

                <textarea
                    id="customer-edit-address"
                    name="address"
                    rows="4"
                    maxlength="5000"
                    x-model="editingCustomer.address"
                    class="w-full resize-none rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                ></textarea>
            </div>

            <div>
                <label for="customer-edit-status" class="mb-2 block text-sm font-semibold text-sp-text">
                    {{ __('Status') }}
                </label>

                <select
                    id="customer-edit-status"
                    name="status"
                    required
                    x-model="editingCustomer.status"
                    class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                >
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </select>
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
                form="customer-edit-form"
                class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
            >
                {{ __('Save changes') }}
            </button>
        </x-slot:footer>
    </x-stockpilot.modal>

</x-stockpilot.admin-page>