@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'toneClasses' => [],
])

<div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $toneClasses['rail'] ?? '' }}"></div>

<div class="relative flex items-center gap-4">

    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition duration-200 group-hover:scale-105 group-hover:rotate-3 {{ $toneClasses['tile'] ?? '' }}">

        @if ($icon)
            <x-stockpilot.icon :name="$icon" class="h-6 w-6" />
        @endif

    </div>

    <div class="min-w-0 flex-1">

        <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
            {{ $label }}
        </p>

        <p class="mt-1 truncate text-xl font-black leading-tight tracking-tight {{ $toneClasses['value'] ?? '' }}">
            {{ $value }}
        </p>

        @if ($hint)
            <p class="mt-0.5 truncate text-[11px] font-semibold text-sp-text-subtle">
                {{ $hint }}
            </p>
        @endif

    </div>

</div>