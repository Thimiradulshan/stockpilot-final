<div
    x-data="{
        open: false,
        left: 0,
        bottom: 0,
        toggle() {
            this.open = ! this.open;

            if (this.open) {
                this.$nextTick(() => this.reposition());
            }
        },
        reposition() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            const width = Math.min(320, window.innerWidth - 16);

            this.left = Math.max(
                8,
                Math.min(rect.left, window.innerWidth - width - 8)
            );

            this.bottom = Math.max(
                8,
                window.innerHeight - rect.top + 8
            );
        },
        close() {
            this.open = false;
        },
    }"
    x-on:resize.window="open && reposition()"
    x-on:scroll.window.passive="open && reposition()"
    class="relative w-full"
>
    <button
        type="button"
        x-ref="trigger"
        @click.stop="toggle()"
        @keydown.escape.window="open = false"
        :aria-expanded="open"
        class="flex w-full items-center gap-3 rounded-xl border border-white/10 bg-white/[0.06] px-3 py-2.5 text-left transition hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/20"
        data-test="sidebar-menu-button"
        aria-label="{{ __('Open profile menu') }}"
    >

        @if (auth()->user()->profile_photo_path)

            <img
                src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                alt="{{ auth()->user()->name }}"
                class="h-10 w-10 shrink-0 rounded-full object-cover object-center ring-1 ring-white/10"
                loading="eager"
            >

        @else

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sp-accent to-sp-danger text-xs font-bold text-white">
                {{ auth()->user()->initials() }}
            </span>

        @endif


        <span
            x-show="sidebarIsExpanded()"
            x-transition.opacity.duration.100ms
            class="min-w-0 flex-1"
        >

            <span class="block truncate text-sm font-bold text-white">
                {{ auth()->user()->name }}
            </span>

            <span class="mt-0.5 block truncate text-xs text-sp-sidebar-muted">
                {{ auth()->user()->email }}
            </span>

        </span>


        <svg
            x-show="sidebarIsExpanded()"
            x-transition.opacity.duration.100ms
            class="h-4 w-4 shrink-0 text-white/50"
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


    <template x-teleport="body">
    <div
        x-cloak
        x-show="open"
        x-transition.origin.bottom.left
        @click.outside="close()"
        :style="`inset-inline-start: ${left}px; bottom: ${bottom}px;`"
        class="fixed z-[70] w-80 overflow-hidden rounded-2xl border border-sp-border bg-sp-surface shadow-2xl"
    >

        <div class="border-b border-sp-border bg-sp-surface-muted px-5 py-5">

            <div class="flex items-center gap-3">

                @if (auth()->user()->profile_photo_path)

                    <img
                        src="{{ Storage::disk('public')->url(auth()->user()->profile_photo_path) }}"
                        alt="{{ auth()->user()->name }}"
                        class="h-12 w-12 shrink-0 rounded-full object-cover object-center shadow-sm"
                    >

                @else

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-sp-accent to-sp-danger text-sm font-bold text-white">
                        {{ auth()->user()->initials() }}
                    </span>

                @endif


                <div class="min-w-0">

                    <p class="truncate text-sm font-bold text-sp-text">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="truncate text-xs text-sp-text-muted">
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
                class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted"
            >
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sp-info-soft text-sp-info-foreground">
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

                {{ __('My Profile') }}
            </a>


            <div class="my-1 border-t border-sp-border"></div>


            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    data-test="logout-button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-semibold text-sp-danger transition hover:bg-sp-danger-soft"
                >

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sp-danger-soft text-sp-danger">

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
    </template>

</div>
