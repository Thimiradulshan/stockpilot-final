@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'value' => null,
    'required' => false,
    'autofocus' => false,
    'autocomplete' => 'current-password',
])

<div
    x-data="{ visible: false }"
    class="grid gap-1.5"
>
    @if ($label)
        <label
            for="{{ $name }}"
            class="text-sm font-medium text-sp-text"
        >
            {{ $label }}

            @if ($required)
                <span class="text-sp-danger" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            x-bind:type="visible ? 'text' : 'password'"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            @required($required)
            @autofocus($autofocus)
            class="block w-full rounded-lg border border-sp-border bg-sp-surface px-3.5 py-2.5 pe-11 text-sm text-sp-text shadow-sm placeholder:text-sp-text-subtle transition focus:border-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:bg-sp-surface dark:text-sp-text"
        />

        <button
            type="button"
            x-on:click="visible = !visible"
            class="absolute inset-y-0 end-0 flex w-10 items-center justify-center text-sp-text-subtle transition hover:text-sp-text focus-visible:outline-none"
            :aria-label="visible ? 'Hide password' : 'Show password'"
        >
            <svg
                x-show="!visible"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                class="h-5 w-5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6 9.75-6 9.75 6 9.75 6-3.75 6-9.75 6S2.25 12 2.25 12Z"/>
                <circle cx="12" cy="12" r="2.5"/>
            </svg>

            <svg
                x-show="visible"
                x-cloak
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                class="h-5 w-5"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.58 10.58a2 2 0 0 0 2.83 2.83"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.17A10.9 10.9 0 0 1 12 5c6 0 9.75 7 9.75 7a19.1 19.1 0 0 1-3.08 3.98"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.61 6.61C4.23 8.28 2.25 12 2.25 12s3.75 7 9.75 7c1.72 0 3.25-.45 4.58-1.12"/>
            </svg>
        </button>
    </div>

    @error($name)
        <p class="text-sm text-sp-danger" role="alert">
            {{ $message }}
        </p>
    @enderror
</div>
