@props([
    'label',
    'icon' => 'plus',
    'click' => null,
])

<button
    type="button"
    @if ($click)
        @click="{{ $click }}"
    @endif
    {{ $attributes->class([
        'group inline-flex items-center gap-2.5 rounded-xl bg-sp-primary px-5 py-3.5 text-sm font-bold text-sp-primary-foreground shadow-lg shadow-sp-primary/25 transition duration-200 hover:-translate-y-0.5 hover:bg-sp-primary-hover hover:shadow-xl hover:shadow-sp-primary/30 focus:outline-none focus:ring-4 focus:ring-sp-primary/20 dark:bg-sp-primary dark:text-sp-primary-foreground dark:hover:bg-sp-primary-hover',
    ]) }}
>

    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/15">
        <x-stockpilot.icon :name="$icon" class="h-4 w-4 transition-transform duration-200 group-hover:rotate-90" />
    </span>

    {{ $label }}

</button>