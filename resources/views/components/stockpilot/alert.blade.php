@props([
    'type' => 'info',
])

@php
    $classes = match ($type) {
        'success' => 'border-sp-success/30 bg-sp-success/10 text-sp-success dark:bg-sp-success/15',
        'warning' => 'border-sp-warning/40 bg-sp-warning/15 text-sp-text',
        'danger', 'error' => 'border-sp-danger/30 bg-sp-danger/10 text-sp-danger dark:bg-sp-danger/15',
        default => 'border-sp-info/30 bg-sp-info/10 text-sp-info dark:bg-sp-info/15',
    };
@endphp

<div
    {{ $attributes->merge([
        'class' => "rounded-lg border px-4 py-3 text-sm {$classes}",
    ]) }}
    role="alert"
>
    {{ $slot }}
</div>
