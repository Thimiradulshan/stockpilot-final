@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'toneClasses' => [],
])

<div class="absolute -right-10 -top-10 h-36 w-36 rounded-full blur-3xl {{ $toneClasses['orbA'] ?? '' }}"></div>

<div class="absolute bottom-0 left-0 h-24 w-24 rounded-full blur-2xl {{ $toneClasses['orbB'] ?? '' }}"></div>

<div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r {{ $toneClasses['rail'] ?? '' }}"></div>

<div class="relative flex items-center justify-between gap-5">

    <div>

        <div class="flex items-center gap-2">

            <span class="h-2 w-2 rounded-full {{ $toneClasses['dot'] ?? '' }}"></span>

            <p class="text-xs font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                {{ $label }}
            </p>

        </div>

        <p class="mt-3 text-[40px] font-black leading-none tracking-tight {{ $toneClasses['value'] ?? '' }}">
            {{ $value }}
        </p>

        @if ($hint)
            <p class="mt-2 text-xs font-semibold text-sp-text-muted {{ $toneClasses['hint'] ?? '' }}">
                {{ $hint }}
            </p>
        @endif

    </div>

    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl transition duration-200 group-hover:scale-110 group-hover:rotate-3 {{ $toneClasses['tile'] ?? '' }}">

        @if ($icon)
            <x-stockpilot.icon :name="$icon" class="h-7 w-7" />
        @endif

    </div>

</div>