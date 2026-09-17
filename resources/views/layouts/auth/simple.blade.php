<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    x-data
    x-bind:class="$store.theme?.theme === 'dark' ? 'dark' : ''"
>
    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-sp-background text-sp-text antialiased dark:bg-sp-dark-background dark:text-sp-dark-text">
        <div class="relative flex min-h-svh items-center justify-center overflow-hidden px-4 py-10 sm:px-6 lg:px-8">

            
            <div
                aria-hidden="true"
                class="pointer-events-none absolute inset-0 overflow-hidden"
            >
                <div class="absolute -left-32 -top-32 h-80 w-80 rounded-full bg-sp-primary/10 blur-3xl"></div>
                <div class="absolute -bottom-32 -right-32 h-80 w-80 rounded-full bg-sp-brand/10 blur-3xl"></div>
            </div>

            <main class="relative z-10 w-full max-w-md">
                <div class="mb-8 flex flex-col items-center">
                    <a
                        href="{{ route('home') }}"
                        wire:navigate
                        class="group inline-flex flex-col items-center gap-3"
                        aria-label="{{ config('app.name', 'StockPilot') }}"
                    >
                        <x-app-logo
                            class="h-12 w-12 transition-transform duration-200 group-hover:scale-105"
                        />

                        <span class="text-xl font-bold tracking-tight text-sp-brand dark:text-white">
                            {{ config('app.name', 'StockPilot') }}
                        </span>
                    </a>
                </div>

                <section
                    class="rounded-2xl border border-sp-border bg-white p-6 shadow-xl shadow-black/5 sm:p-8 dark:border-sp-dark-border dark:bg-sp-dark-surface dark:shadow-black/20"
                >
                    {{ $slot }}
                </section>

                <p class="mt-6 text-center text-xs text-sp-muted dark:text-sp-dark-muted">
                    {{ config('app.name', 'StockPilot') }}
                    &middot;
                    Sales, Inventory &amp; Business Management
                </p>
            </main>
        </div>
    </body>
</html>

