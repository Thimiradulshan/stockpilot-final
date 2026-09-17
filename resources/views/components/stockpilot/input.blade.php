@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'autofocus' => false,
    'autocomplete' => null,
    'disabled' => false,
])

@php
    $hasError = $errors->has($name);

    $inputClasses = implode(' ', [
        'block w-full rounded-lg border bg-sp-surface px-3.5 py-2.5 text-sm text-sp-text shadow-sm',
        'placeholder:text-sp-text-subtle',
        'transition',
        'focus:border-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20',
        'disabled:cursor-not-allowed disabled:bg-sp-surface-muted disabled:opacity-60',
        $hasError
            ? 'border-sp-danger focus:border-sp-danger focus:ring-sp-danger/20'
            : 'border-sp-border',
        'dark:bg-sp-surface dark:text-sp-text dark:placeholder:text-sp-text-subtle',
    ]);
@endphp

<div class="grid gap-1.5">
    @if ($label)
        <label
            for="{{ $name }}"
            class="text-sm font-medium text-sp-text"
        >
            {{ $label }}

            @if ($required)
                <span
                    class="text-sp-danger"
                    aria-hidden="true"
                >*</span>
            @endif
        </label>
    @endif

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        @autofocus($autofocus)
        @disabled($disabled)
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        {{ $attributes->merge(['class' => $inputClasses]) }}
    />

    @error($name)
        <p class="text-sm text-sp-danger" role="alert">
            {{ $message }}
        </p>
    @enderror
</div>
