@props([
    'id',
    'placeholder' => null,
])

<label
    for="{{ $id }}"
    class="sr-only"
>
    {{ $placeholder }}
</label>

<div class="relative w-full">

    <div class="pointer-events-none absolute start-3.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-sp-primary/10 text-sp-primary dark:bg-sp-primary/10 dark:text-sp-success">

        <svg
            class="h-4 w-4"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.8"
        >
            <circle cx="11" cy="11" r="7"/>
            <path
                stroke-linecap="round"
                d="m20 20-4-4"
            />
        </svg>

    </div>

    <input
        id="{{ $id }}"
        type="search"
        @if ($placeholder)
            placeholder="{{ $placeholder }}"
        @endif
        {{ $attributes->merge([
            'class' => 'w-full rounded-xl border border-sp-border-strong bg-sp-surface py-3.5 pe-4 ps-14 text-sm font-semibold text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-4 focus:ring-sp-primary/10 dark:border-sp-border-strong dark:bg-sp-surface-soft dark:text-sp-text dark:placeholder:text-sp-text-subtle dark:focus:border-sp-success dark:focus:ring-sp-success/10',
        ]) }}
    />

</div>