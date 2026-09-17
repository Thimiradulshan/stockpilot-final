<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data="stockPilotShell()"
>

    <head>
        @include('partials.head')
    </head>


    <body class="min-h-screen bg-sp-background text-sp-text antialiased">

        
        <div
            x-cloak
            x-show="mobileSidebarOpen"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-40 bg-[#143732]/60 lg:hidden"
            @click="closeMobileSidebar()"
            aria-hidden="true"
        ></div>


        
        <aside
            class="fixed inset-y-0 start-0 z-50 flex flex-col overflow-hidden bg-sp-sidebar text-white shadow-2xl transition-[width,transform] duration-300 ease-out"
            :class="[
                sidebarIsExpanded()
                    ? 'lg:w-[300px]'
                    : 'lg:w-[86px]',

                mobileSidebarOpen
                    ? 'w-[300px] translate-x-0'
                    : '-translate-x-full lg:translate-x-0'
            ]"
            aria-label="{{ __('Main navigation') }}"
        >

            
            <div class="flex h-[76px] shrink-0 items-center border-b border-white/10 px-4">

                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    @click="
                        if (window.innerWidth >= 1024 && sidebarCollapsed) {
                            $event.preventDefault();
                            toggleSidebar();
                        }
                    "
                    class="flex min-w-0 flex-1 items-center gap-3"
                    aria-label="{{ config('app.name', 'StockPilot') }}"
                >

                    <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white shadow-lg shadow-black/10">

                        <span class="absolute bottom-2 left-2 h-4.5 w-4.5 rounded-md bg-sp-accent"></span>

                        <span class="absolute right-2 top-2 h-4.5 w-4.5 rounded-md bg-sp-primary"></span>

                    </span>


                    <span
                        x-show="sidebarIsExpanded()"
                        x-transition.opacity.duration.150ms
                        class="min-w-0"
                    >

                        <span class="block truncate text-[18px] font-extrabold tracking-tight text-white">
                            StockPilot
                        </span>

                        <span class="mt-0.5 block truncate text-[10px] font-semibold uppercase tracking-[0.14em] text-sp-sidebar-muted">
                            Business Management
                        </span>

                    </span>

                </a>


                <button
                    type="button"
                    @click="toggleSidebar()"
                    class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white/65 transition hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/30 lg:flex"
                    :aria-label="sidebarCollapsed ? '{{ __('Expand sidebar') }}' : '{{ __('Collapse sidebar') }}'"
                >

                    <svg
                        class="h-5 w-5 transition-transform duration-300"
                        :class="sidebarCollapsed ? '' : 'rotate-180'"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="m9 18 6-6-6-6"
                        />
                    </svg>

                </button>


                <button
                    type="button"
                    @click="closeMobileSidebar()"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white/65 transition hover:bg-white/10 hover:text-white lg:hidden"
                    aria-label="{{ __('Close navigation') }}"
                >

                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <path
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6 6 18"
                        />
                    </svg>

                </button>

            </div>


            
            <nav class="flex-1 overflow-y-auto px-3 py-6">

                <div
                    x-show="sidebarIsExpanded()"
                    x-transition.opacity.duration.150ms
                    class="mb-3 px-3 text-[11px] font-bold uppercase tracking-[0.16em] text-sp-sidebar-muted"
                >
                    {{ __('Workspace') }}
                </div>


                
                <a
                    href="{{ route('dashboard') }}"
                    wire:navigate
                    @click="closeMobileSidebar()"
                    @class([
                        'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                        'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('dashboard'),
                        'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('dashboard'),
                    ])
                    :title="sidebarIsExpanded() ? null : '{{ __('Dashboard') }}'"
                >

                    <span class="flex h-6 w-6 shrink-0 items-center justify-center">
                        <x-stockpilot.icon
                            name="dashboard"
                            class="h-[22px] w-[22px]"
                        />
                    </span>

                    <span
                        x-show="sidebarIsExpanded()"
                        x-transition.opacity.duration.100ms
                        class="truncate"
                    >
                        {{ __('Dashboard') }}
                    </span>

                </a>


                
                @can('viewAny', App\Models\Product::class)

                    <a
                        href="{{ route('admin.products.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.products.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.products.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Products') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="products"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Products') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Category::class)

                    <a
                        href="{{ route('admin.categories.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.categories.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.categories.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Categories') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="categories"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Categories') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Supplier::class)

                    <a
                        href="{{ route('admin.suppliers.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.suppliers.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.suppliers.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Suppliers') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="suppliers"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Suppliers') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Customer::class)

                    <a
                        href="{{ route('admin.customers.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.customers.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.customers.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Customers') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="customers"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Customers') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Purchase::class)

                    <div
                        x-show="sidebarIsExpanded()"
                        x-transition.opacity.duration.150ms
                        class="mb-3 mt-8 px-3 pt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-sp-sidebar-muted"
                    >
                        {{ __('Operations') }}
                    </div>


                    <a
                        href="{{ route('admin.purchases.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.purchases.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.purchases.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Purchasing') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="purchasing"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Purchasing') }}
                        </span>

                    </a>

                @endcan


                
                @can('create', App\Models\Invoice::class)

                    <a
                        href="{{ route('admin.pos.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.pos.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.pos.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Point of Sale') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="shopping-cart"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Point of Sale') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Invoice::class)

                    <a
                        href="{{ route('admin.sales.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.sales.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.sales.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Sales') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="receipt"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Sales') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Invoice::class)

                    <a
                        href="{{ route('admin.reports.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.reports.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.reports.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Reports') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="reports"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Reports') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Product::class)

                    <a
                        href="{{ route('admin.ledger.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.ledger.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.ledger.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Stock ledger') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="ledger"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Stock ledger') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\Invoice::class)

                    <a
                        href="{{ route('admin.statements.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.statements.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.statements.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Customer statements') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="customers"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Customer statements') }}
                        </span>

                    </a>

                @endcan


                
                @can('viewAny', App\Models\User::class)

                    <div
                        x-show="sidebarIsExpanded()"
                        x-transition.opacity.duration.150ms
                        class="mb-3 mt-8 px-3 pt-1 text-[11px] font-bold uppercase tracking-[0.16em] text-sp-sidebar-muted"
                    >
                        {{ __('Administration') }}
                    </div>


                    <a
                        href="{{ route('admin.users.index') }}"
                        wire:navigate
                        @click="closeMobileSidebar()"
                        @class([
                            'group mb-1.5 flex items-center gap-3 rounded-xl px-3.5 py-3 text-[15px] font-semibold transition-all',
                            'bg-sp-sidebar-active text-white shadow-lg shadow-black/10' => request()->routeIs('admin.users.*'),
                            'text-sp-sidebar-text hover:bg-sp-sidebar-hover hover:text-white' => ! request()->routeIs('admin.users.*'),
                        ])
                        :title="sidebarIsExpanded() ? null : '{{ __('Users') }}'"
                    >

                        <span class="flex h-6 w-6 shrink-0 items-center justify-center">

                            <x-stockpilot.icon
                                name="users"
                                class="h-[22px] w-[22px]"
                            />

                        </span>

                        <span
                            x-show="sidebarIsExpanded()"
                            x-transition.opacity.duration.100ms
                            class="truncate"
                        >
                            {{ __('Users') }}
                        </span>

                    </a>

                @endcan

            </nav>


            
            <div class="shrink-0 border-t border-white/10 p-3">

                <x-desktop-user-menu />

            </div>

        </aside>


        
        <div
            class="min-h-screen transition-[padding] duration-300"
            :class="sidebarIsExpanded() ? 'lg:pl-[300px]' : 'lg:pl-[86px]'"
        >

            
            <header class="sticky top-0 z-30 border-b border-sp-border bg-sp-surface/95 backdrop-blur-sm">

                <div class="flex h-[76px] items-center gap-3 px-5 sm:px-7">

                    <button
                        type="button"
                        @click="openMobileSidebar()"
                        class="flex h-11 w-11 items-center justify-center rounded-xl text-sp-text-muted transition hover:bg-sp-surface-muted hover:text-sp-text lg:hidden"
                        aria-label="{{ __('Open navigation') }}"
                    >

                        <svg
                            class="h-6 w-6"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path
                                stroke-linecap="round"
                                d="M4 7h16M4 12h16M4 17h16"
                            />
                        </svg>

                    </button>


                    <div class="min-w-0 flex-1">

                        <p class="truncate text-[17px] font-bold text-sp-brand-dark dark:text-sp-text">
                            {{ config('app.name', 'StockPilot') }}
                        </p>

                        <p class="hidden truncate text-sm font-medium text-sp-text-muted sm:block">
                            {{ __('Sales, inventory & business management') }}
                        </p>

                    </div>


                    
                    <div
                        x-data="{ open: false }"
                        class="relative"
                    >

                        <button
                            type="button"
                            @click="open = !open"
                            class="flex h-11 w-11 items-center justify-center rounded-xl text-sp-text-muted transition hover:bg-sp-surface-muted hover:text-sp-text"
                            aria-label="{{ __('Appearance') }}"
                        >

                            <template x-if="$store.theme.theme === 'light'">

                                <x-stockpilot.icon
                                    name="sun"
                                    class="h-5 w-5"
                                />

                            </template>


                            <template x-if="$store.theme.theme === 'dark'">

                                <x-stockpilot.icon
                                    name="moon"
                                    class="h-5 w-5"
                                />

                            </template>


                            <template x-if="$store.theme.theme === 'system'">

                                <x-stockpilot.icon
                                    name="system"
                                    class="h-5 w-5"
                                />

                            </template>

                        </button>


                        <div
                            x-cloak
                            x-show="open"
                            x-transition.origin.top.right
                            @click.outside="open = false"
                            class="absolute end-0 top-full z-50 mt-2 w-48 overflow-hidden rounded-xl border border-sp-border bg-sp-surface p-1.5 shadow-xl"
                        >

                            <button
                                type="button"
                                @click="$store.theme.set('light'); open = false"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold text-sp-text hover:bg-sp-surface-muted"
                            >

                                <x-stockpilot.icon
                                    name="sun"
                                    class="h-4 w-4"
                                />

                                {{ __('Light') }}

                            </button>


                            <button
                                type="button"
                                @click="$store.theme.set('dark'); open = false"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold text-sp-text hover:bg-sp-surface-muted"
                            >

                                <x-stockpilot.icon
                                    name="moon"
                                    class="h-4 w-4"
                                />

                                {{ __('Dark') }}

                            </button>


                            <button
                                type="button"
                                @click="$store.theme.set('system'); open = false"
                                class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-sm font-semibold text-sp-text hover:bg-sp-surface-muted"
                            >

                                <x-stockpilot.icon
                                    name="system"
                                    class="h-4 w-4"
                                />

                                {{ __('System') }}

                            </button>

                        </div>

                    </div>


                    
                    <button
                        type="button"
                        class="hidden h-11 w-11 items-center justify-center rounded-xl text-sp-text-muted transition hover:bg-sp-surface-muted hover:text-sp-text sm:flex"
                        aria-label="{{ __('Notifications') }}"
                    >

                        <x-stockpilot.icon
                            name="bell"
                            class="h-5 w-5"
                        />

                    </button>


                    
                    <x-topbar-user-menu />

                </div>

            </header>


            <main class="min-w-0">
                {{ $slot }}
            </main>

        </div>


        @livewireScripts

        @stack('scripts')

    </body>

</html>


