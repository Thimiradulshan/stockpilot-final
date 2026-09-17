@props([
    'name',
    'label' => null,
    'checked' => false,
    'disabled' => false,
])

<div class="flex items-start gap-3">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="checkbox"
        value="1"
        @checked(old($name, $checked))
        @disabled($disabled)
        {{ $attributes->merge([
            'class' => 'mt-0.5 h-4 w-4 rounded border-sp-border text-sp-primary shadow-sm focus:ring-2 focus:ring-sp-primary/30 disabled:cursor-not-allowed disabled:opacity-60',
        ]) }}
    />

    @if ($label)
        <label
            for="{{ $name }}"
            class="cursor-pointer text-sm font-medium text-sp-text-muted select-none"
        >
            {{ $label }}
        </label>
    @endif
</div>

@error($name)
    <p class="text-sm text-sp-danger" role="alert">
        {{ $message }}
    </p>
@enderror
