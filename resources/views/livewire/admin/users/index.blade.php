<x-stockpilot.admin-page
    x-data="{
        createOpen: false,
        editOpen: false,
        editingUser: null,

        openCreate() {
            this.editOpen = false;
            this.editingUser = null;
            this.createOpen = true;
        },

        openEdit(user) {
            this.createOpen = false;
            this.editingUser = user;
            this.editOpen = true;
        },

        closeModals() {
            this.createOpen = false;
            this.editOpen = false;
            this.editingUser = null;
        },
    }"
    escapeAction="closeModals()"
>

    <x-stockpilot.page-header
        :section-label="__('Administration')"
        :section-current="__('Users')"
        :title="__('Users')"
        :subtitle="__('Team management')"
        :description="__('Create and manage the team members who use StockPilot.')"
        :tags="[
            ['label' => __('Accounts'), 'tone' => 'primary'],
            ['label' => __('Admin only'), 'tone' => 'info'],
        ]"
    >
        <x-slot:actions>
            @can('create', \App\Models\User::class)
                <x-stockpilot.primary-action
                    :label="__('Add user')"
                    icon="users"
                    click="openCreate()"
                />
            @endcan
        </x-slot:actions>
    </x-stockpilot.page-header>

    <x-stockpilot.toast-alerts />

    <section aria-label="{{ __('User summary') }}">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

            <x-stockpilot.kpi-card
                :label="__('Total users')"
                :value="number_format($totalUsers)"
                :hint="__('Entire team roster')"
                icon="users"
                tone="primary"
                action="clearFilters()"
                :active="! $this->hasActiveFilters()"
            />

            <x-stockpilot.kpi-card
                :label="__('Active users')"
                :value="number_format($activeUsers)"
                :hint="__('Able to sign in')"
                icon="check"
                tone="success"
                action="$set('status', 'active')"
                :active="$status === 'active'"
            />

            <x-stockpilot.kpi-card
                :label="__('Inactive users')"
                :value="number_format($inactiveUsers)"
                :hint="__('Signed out immediately')"
                icon="x-circle"
                tone="info"
                action="$set('status', 'inactive')"
                :active="$status === 'inactive'"
            />

        </div>
    </section>

    <x-stockpilot.filter-bar aria-label="{{ __('User filters') }}">

        <div class="w-full lg:max-w-2xl">
            <x-stockpilot.search-input
                id="user-search"
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search by name or email...')"
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

            <select
                wire:model.live="role"
                class="rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-3.5 text-sm font-bold text-sp-text shadow-sm outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
            >
                <option value="">{{ __('All roles') }}</option>
                <option value="admin">{{ __('Admin') }}</option>
                <option value="sales">{{ __('Sales User') }}</option>
                <option value="stock">{{ __('Inventory User') }}</option>
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

    <x-stockpilot.page-table mobile="lg" aria-label="{{ __('User roster') }}">

        <table class="min-w-full border-collapse">

            <thead class="bg-gradient-to-r from-sp-primary/15 via-sp-surface-muted to-sp-info-soft/70 dark:from-sp-brand-dark dark:via-sp-surface-muted dark:to-sp-info-soft">

                <tr class="border-b border-sp-border-strong dark:border-sp-border-strong">

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('User') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Role') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Status') }}
                    </th>

                    <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Joined') }}
                    </th>

                    <th scope="col" class="w-[180px] px-6 py-4 text-left text-xs font-extrabold uppercase tracking-[0.10em] text-sp-text">
                        {{ __('Actions') }}
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-sp-border dark:divide-sp-border">

                @forelse ($users as $user)

                    <tr
                        wire:key="user-row-{{ $user->id }}"
                        class="group transition duration-150 odd:bg-sp-surface even:bg-sp-surface-muted/40 hover:bg-sp-primary/[0.04] dark:odd:bg-sp-surface dark:even:bg-sp-surface-muted dark:hover:bg-sp-brand-dark/70"
                    >

                        <td class="px-6 py-5">

                            <div class="flex min-w-0 items-center gap-3">

                                <div class="relative flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary shadow-sm dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

                                    <span class="absolute -right-2 -top-2 h-7 w-7 rounded-full bg-sp-accent/25"></span>

                                    <span class="relative text-sm font-extrabold">
                                        {{ $user->initials() }}
                                    </span>

                                </div>

                                <div class="min-w-0">

                                    <p class="truncate text-[15px] font-extrabold text-sp-text">
                                        {{ $user->name }}
                                        @if ($user->id === $currentUserId)
                                            <span class="ml-1 text-xs font-bold normal-case text-sp-text-subtle">
                                                ({{ __('you') }})
                                            </span>
                                        @endif
                                    </p>

                                    <p class="mt-1 truncate text-xs font-semibold text-sp-text-subtle">
                                        {{ $user->email }}
                                    </p>

                                </div>

                            </div>

                        </td>

                        <td class="px-6 py-5">
                            <span class="inline-flex items-center rounded-xl bg-sp-info-soft px-3 py-2 text-sm font-extrabold text-sp-info-foreground shadow-sm dark:bg-sp-info-soft dark:text-sp-info">
                                {{ $user->roleEnum()?->label() ?? __('Unknown') }}
                            </span>
                        </td>

                        <td class="px-6 py-5 text-center">

                            @if ($user->status === 'active')
                                <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                            @else
                                <x-stockpilot.status-badge :label="__('Inactive')" tone="warning" />
                            @endif

                        </td>

                        <td class="px-6 py-5 text-center">
                            <p class="text-sm font-semibold text-sp-text">
                                {{ $user->created_at->toFormattedDateString() }}
                            </p>
                        </td>

                        <td class="px-6 py-5">

                            @can('update', $user)

                                <button
                                    type="button"
                                    @click="openEdit(@js([
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'role' => $user->role,
                                        'status' => $user->status,
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
                        <td colspan="5" class="px-6 py-20 text-center">

                            <x-stockpilot.empty-state
                                icon="users"
                                :title="$this->hasActiveFilters() ? __('No matching users') : __('No users yet')"
                                :message="$this->hasActiveFilters() ? __('Try changing your search or filters.') : __('Create your first team member.')"
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

                                @can('create', \App\Models\User::class)
                                    @if (! $this->hasActiveFilters())
                                        <button
                                            type="button"
                                            @click="openCreate()"
                                            class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground shadow-md transition hover:bg-sp-primary-hover"
                                        >
                                            {{ __('Add user') }}
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

        @forelse ($users as $user)

            <article class="rounded-2xl border border-sp-border bg-sp-surface p-4 shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <div class="flex items-start justify-between gap-3">

                    <div class="flex min-w-0 items-center gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sp-primary/10 text-sm font-bold text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">
                            {{ $user->initials() }}
                        </div>

                        <div class="min-w-0">

                            <h3 class="truncate text-sm font-bold text-sp-text">
                                {{ $user->name }}
                            </h3>

                            <p class="mt-0.5 truncate text-xs text-sp-text-subtle">
                                {{ $user->email }}
                            </p>

                        </div>

                    </div>

                    @if ($user->status === 'active')
                        <x-stockpilot.status-badge :label="__('Active')" tone="success" />
                    @else
                        <x-stockpilot.status-badge :label="__('Inactive')" tone="warning" />
                    @endif

                </div>

                <div class="mt-4 flex items-center justify-between rounded-xl bg-sp-surface-muted px-3 py-3 dark:bg-sp-surface-muted">

                    <span class="inline-flex items-center rounded-lg bg-sp-info-soft px-2.5 py-1.5 text-xs font-extrabold text-sp-info-foreground dark:bg-sp-info-soft dark:text-sp-info">
                        {{ $user->roleEnum()?->label() ?? __('Unknown') }}
                    </span>

                    <p class="text-xs font-semibold text-sp-text-subtle">
                        {{ __('Joined') }} {{ $user->created_at->toFormattedDateString() }}
                    </p>

                </div>

                @can('update', $user)
                    <button
                        type="button"
                        @click="openEdit(@js([
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role,
                            'status' => $user->status,
                        ]))"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-sp-border-strong bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
                    >
                        <x-stockpilot.icon name="edit" class="h-4 w-4" />
                        {{ __('Edit user') }}
                    </button>
                @endcan

            </article>

        @empty

            <div class="rounded-2xl border border-sp-border bg-sp-surface px-6 py-14 text-center shadow-sm dark:border-sp-border dark:bg-sp-surface">

                <x-stockpilot.empty-state
                    icon="users"
                    :title="__('No users found')"
                    :message="__('Try changing your search or filters.')"
                />

            </div>

        @endforelse

        @if ($users->hasPages())
            <div>
                {{ $users->links() }}
            </div>
        @endif

    </div>

    <x-stockpilot.pagination-bar
        :paginator="$users"
        :item-label="__('users')"
    />

    @can('create', \App\Models\User::class)
        <x-stockpilot.modal
            open="createOpen"
            close="closeModals()"
            :title="__('Add user')"
            :subtitle="__('Create a team member account.')"
            :eyebrow="__('Administration')"
            icon="users"
            heading-id="create-user-title"
            dialog-label="{{ __('Add user') }}"
            width="max-w-2xl"
        >
            <form
                id="user-create-form"
                method="POST"
                action="{{ route('admin.users.store') }}"
                class="space-y-5 px-6 py-6"
            >
                @csrf

                <div class="grid gap-5 md:grid-cols-2">

                    <div>
                        <label for="user-create-name" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Full name') }}
                        </label>

                        <input
                            id="user-create-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="user-create-email" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Email') }}
                        </label>

                        <input
                            id="user-create-email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="user-create-role" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Role') }}
                        </label>

                        <select
                            id="user-create-role"
                            name="role"
                            required
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                            <option value="admin" @selected(old('role', 'sales') === 'admin')>
                                {{ __('Admin') }}
                            </option>

                            <option value="sales" @selected(old('role', 'sales') === 'sales')>
                                {{ __('Sales User') }}
                            </option>

                            <option value="stock" @selected(old('role', 'sales') === 'stock')>
                                {{ __('Inventory User') }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="user-create-status" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Status') }}
                        </label>

                        <select
                            id="user-create-status"
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

                    <div>
                        <label for="user-create-password" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Password') }}
                        </label>

                        <input
                            id="user-create-password"
                            name="password"
                            type="password"
                            maxlength="255"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
                    </div>

                    <div>
                        <label for="user-create-password-confirmation" class="mb-2 block text-sm font-semibold text-sp-text">
                            {{ __('Confirm password') }}
                        </label>

                        <input
                            id="user-create-password-confirmation"
                            name="password_confirmation"
                            type="password"
                            maxlength="255"
                            required
                            autocomplete="new-password"
                            class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                        >
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
                    form="user-create-form"
                    class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
                >
                    {{ __('Create user') }}
                </button>
            </x-slot:footer>
        </x-stockpilot.modal>
    @endcan

    <x-stockpilot.modal
        open="editOpen"
        close="closeModals()"
        :title="__('Edit user')"
        :subtitle="__('Update team member details.')"
        :eyebrow="__('Administration')"
        icon="users"
        heading-id="edit-user-title"
        dialog-label="{{ __('Edit user') }}"
        width="max-w-2xl"
    >
        <form
            id="user-edit-form"
            method="POST"
            x-bind:action="editingUser ? `{{ url('/admin/users') }}/${editingUser.id}` : '#'"
            class="space-y-5 px-6 py-6"
        >
            @csrf
            @method('PATCH')

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label for="user-edit-name" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Full name') }}
                    </label>

                    <input
                        id="user-edit-name"
                        name="name"
                        type="text"
                        required
                        maxlength="255"
                        x-model="editingUser.name"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div>
                    <label for="user-edit-email" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Email') }}
                    </label>

                    <input
                        id="user-edit-email"
                        name="email"
                        type="email"
                        required
                        maxlength="255"
                        x-model="editingUser.email"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                </div>

                <div>
                    <label for="user-edit-role" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Role') }}
                    </label>

                    <select
                        id="user-edit-role"
                        name="role"
                        required
                        x-model="editingUser.role"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                        <option value="admin">{{ __('Admin') }}</option>
                        <option value="sales">{{ __('Sales User') }}</option>
                        <option value="stock">{{ __('Inventory User') }}</option>
                    </select>
                </div>

                <div>
                    <label for="user-edit-status" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Status') }}
                    </label>

                    <select
                        id="user-edit-status"
                        name="status"
                        required
                        x-model="editingUser.status"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>

                <div>
                    <label for="user-edit-password" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('New password') }}
                    </label>

                    <input
                        id="user-edit-password"
                        name="password"
                        type="password"
                        maxlength="255"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
                    <p class="mt-1.5 text-xs font-semibold text-sp-text-subtle">
                        {{ __('Leave blank to keep the current password.') }}
                    </p>
                </div>

                <div>
                    <label for="user-edit-password-confirmation" class="mb-2 block text-sm font-semibold text-sp-text">
                        {{ __('Confirm new password') }}
                    </label>

                    <input
                        id="user-edit-password-confirmation"
                        name="password_confirmation"
                        type="password"
                        maxlength="255"
                        autocomplete="new-password"
                        class="w-full rounded-xl border border-sp-border bg-sp-surface-muted px-3.5 py-3 text-sm text-sp-text outline-none transition focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border dark:bg-sp-surface-soft dark:text-sp-text dark:focus:border-sp-success"
                    >
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
                form="user-edit-form"
                class="rounded-xl bg-sp-primary px-4 py-2.5 text-sm font-bold text-sp-primary-foreground transition hover:bg-sp-primary-hover focus:outline-none focus:ring-4 focus:ring-sp-primary/15 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover"
            >
                {{ __('Save changes') }}
            </button>
        </x-slot:footer>
    </x-stockpilot.modal>

</x-stockpilot.admin-page>