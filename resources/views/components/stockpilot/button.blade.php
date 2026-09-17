@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg border font-semibold transition duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary focus-visible:ring-offset-2 focus-visible:ring-offset-sp-background disabled:cursor-not-allowed disabled:opacity-60';

    $variantClasses = match ($variant) {
        'secondary' => 'border-sp-border bg-sp-surface text-sp-text hover:bg-sp-surface-muted dark:border-sp-border dark:bg-sp-surface dark:text-sp-text dark:hover:bg-sp-surface-muted',
        'danger' => 'border-transparent bg-sp-danger text-white hover:brightness-95',
        'ghost' => 'border-transparent bg-transparent text-sp-text-muted hover:bg-sp-surface-muted hover:text-sp-text',
        default => 'border-transparent bg-sp-primary text-sp-primary-foreground hover:brightness-95',
    };

    $sizeClasses = match ($size) {
        'sm' => 'min-h-9 px-3 text-sm',
        'lg' => 'min-h-12 px-5 text-base',
        default => 'min-h-10 px-4 text-sm',
    };
@endphp

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => "{$base} {$variantClasses} {$sizeClasses}",
    ]) }}
>
    {{ $slot }}
</button>
