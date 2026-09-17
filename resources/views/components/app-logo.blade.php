@props([
    'sidebar' => false,
    'href' => null,
])

<a
    href="{{ $href ?? route('dashboard') }}"
    wire:navigate
    {{ $attributes->merge([
        'class' => 'group inline-flex min-w-0 shrink-0 items-center gap-3',
    ]) }}
    aria-label="{{ config('app.name', 'StockPilot') }}"
>
    <span
        class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sp-primary shadow-sm ring-1 ring-white/10"
    >
        <span class="absolute bottom-2 left-2 h-4 w-4 rounded-md bg-sp-accent"></span>
        <span class="absolute right-2 top-2 h-4 w-4 rounded-md bg-white"></span>
    </span>

    <span
        x-show="{{ $sidebar ? 'sidebarIsExpanded()' : 'true' }}"
        x-transition.opacity.duration.150ms
        @class([
            'min-w-0 truncate text-[15px] font-extrabold tracking-tight',
            'text-white' => $sidebar,
            'text-sp-brand-dark dark:text-sp-text' => ! $sidebar,
        ])
    >
        {{ config('app.name', 'StockPilot') }}
    </span>
</a>
