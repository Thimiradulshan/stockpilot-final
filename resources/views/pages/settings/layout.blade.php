<div class="w-full">
    <div class="overflow-hidden rounded-xl border border-sp-border bg-sp-surface shadow-sm">
        <div class="flex items-center gap-1 overflow-x-auto border-b border-sp-border px-3 sm:px-4">
            <a
                href="{{ route('profile.edit') }}"
                wire:navigate
                @class([
                    'relative inline-flex shrink-0 items-center gap-2 px-4 py-3 text-sm font-semibold transition-colors',
                    'text-sp-primary' => request()->routeIs('profile.*'),
                    'text-sp-text-muted hover:text-sp-text' => ! request()->routeIs('profile.*'),
                ])
            >
                <x-stockpilot.icon
                    name="user"
                    class="h-4 w-4"
                />

                {{ __('Profile') }}

                @if (request()->routeIs('profile.*'))
                    <span
                        class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-sp-primary"
                        aria-hidden="true"
                    ></span>
                @endif
            </a>

            @if (Route::has('security.edit'))
                <a
                    href="{{ route('security.edit') }}"
                    wire:navigate
                    @class([
                        'relative inline-flex shrink-0 items-center gap-2 px-4 py-3 text-sm font-semibold transition-colors',
                        'text-sp-primary' => request()->routeIs('security.*'),
                        'text-sp-text-muted hover:text-sp-text' => ! request()->routeIs('security.*'),
                    ])
                >
                    <x-stockpilot.icon
                        name="settings"
                        class="h-4 w-4"
                    />

                    {{ __('Security') }}

                    @if (request()->routeIs('security.*'))
                        <span
                            class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-sp-primary"
                            aria-hidden="true"
                        ></span>
                    @endif
                </a>
            @endif

            @if (Route::has('appearance.edit'))
                <a
                    href="{{ route('appearance.edit') }}"
                    wire:navigate
                    @class([
                        'relative inline-flex shrink-0 items-center gap-2 px-4 py-3 text-sm font-semibold transition-colors',
                        'text-sp-primary' => request()->routeIs('appearance.*'),
                        'text-sp-text-muted hover:text-sp-text' => ! request()->routeIs('appearance.*'),
                    ])
                >
                    <x-stockpilot.icon
                        name="sun"
                        class="h-4 w-4"
                    />

                    {{ __('Appearance') }}

                    @if (request()->routeIs('appearance.*'))
                        <span
                            class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-sp-primary"
                            aria-hidden="true"
                        ></span>
                    @endif
                </a>
            @endif
        </div>

        <div class="p-5 sm:p-7">
            @if (!empty($heading ?? ''))
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-sp-brand-dark dark:text-white">
                        {{ $heading }}
                    </h1>

                    @if (!empty($subheading ?? ''))
                        <p class="mt-1 text-sm leading-6 text-sp-text-muted">
                            {{ $subheading }}
                        </p>
                    @endif
                </div>
            @endif

            <div class="mt-6 w-full">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
