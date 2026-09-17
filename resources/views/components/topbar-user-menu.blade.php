<div
    x-data="{ open: false }"
    class="relative"
>
    <button
        type="button"
        @click="open = !open"
        @keydown.escape.window="open = false"
        :aria-expanded="open"
        aria-haspopup="menu"
        aria-label="{{ __('Open profile menu') }}"
        class="flex items-center gap-2 rounded-xl p-1.5 transition hover:bg-sp-surface-muted focus:outline-none focus:ring-2 focus:ring-sp-primary/20"
    >

        @if (auth()->user()->profile_photo_path)

            <img
                src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                alt="{{ auth()->user()->name }}"
                class="h-10 w-10 shrink-0 rounded-full object-cover object-center shadow-sm ring-2 ring-sp-primary/10"
                loading="eager"
            >

        @else

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sp-accent to-sp-danger text-sm font-bold text-white shadow-sm">
                {{ auth()->user()->initials() }}
            </span>

        @endif


        <span class="hidden text-left sm:block">

            <span class="block max-w-36 truncate text-sm font-bold text-sp-text">
                {{ auth()->user()->name }}
            </span>

            <span class="block text-xs font-medium text-sp-text-subtle">
                StockPilot
            </span>

        </span>


        <svg
            class="hidden h-4 w-4 text-sp-text-muted sm:block"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m6 9 6 6 6-6"
            />
        </svg>

    </button>


    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        @click.outside="open = false"
        class="absolute end-0 top-full z-50 mt-2 w-80 overflow-hidden rounded-2xl border border-sp-border bg-sp-surface shadow-2xl"
        role="menu"
    >

        <div class="border-b border-sp-border bg-sp-surface-muted px-5 py-5">

            <div class="flex items-center gap-3">

                @if (auth()->user()->profile_photo_path)

                    <img
                        src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                        alt="{{ auth()->user()->name }}"
                        class="h-14 w-14 shrink-0 rounded-full object-cover object-center shadow-sm"
                    >

                @else

                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sp-accent to-sp-danger text-sm font-bold text-white">
                        {{ auth()->user()->initials() }}
                    </span>

                @endif


                <div class="min-w-0">

                    <p class="truncate text-sm font-bold text-sp-text">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="mt-0.5 truncate text-xs text-sp-text-muted">
                        {{ auth()->user()->email }}
                    </p>

                </div>

            </div>

        </div>


        <div class="p-2">

            <a
                href="{{ route('profile.edit') }}"
                wire:navigate
                @click="open = false"
                role="menuitem"
                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
            >

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sp-info-soft text-sp-info-foreground">

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="12" cy="8" r="3"/>
                        <path d="M5 21c.8-4 3.1-6 7-6s6.2 2 7 6"/>
                    </svg>

                </span>

                <span>

                    <span class="block">
                        {{ __('My Profile') }}
                    </span>

                    <span class="mt-0.5 block text-xs font-normal text-sp-text-muted">
                        {{ __('Manage your account information') }}
                    </span>

                </span>

            </a>


            <div class="my-1 border-t border-sp-border"></div>


            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    role="menuitem"
                    data-test="topbar-logout-button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-sp-danger transition hover:bg-sp-danger-soft"
                >

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sp-danger-soft text-sp-danger">
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <path d="M10 17l5-5-5-5"/>
                            <path d="M15 12H3"/>
                            <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"/>
                        </svg>
                    </span>

                    {{ __('Log out') }}

                </button>

            </form>

        </div>

    </div>

</div>
