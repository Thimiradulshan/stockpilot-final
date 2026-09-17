@props([
    'tone' => 'success',
    'label',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-sp-success-soft text-sp-success-foreground dark:bg-sp-success-soft dark:text-sp-success',
        'info' => 'bg-sp-info-soft text-sp-info-foreground dark:bg-sp-info-soft dark:text-sp-info',
        'danger' => 'bg-sp-danger-soft text-sp-danger dark:bg-sp-danger-soft dark:text-sp-danger',
        'warning' => 'bg-sp-warning-soft text-sp-warning-foreground dark:bg-sp-warning-soft dark:text-sp-warning',
        'muted' => 'bg-sp-surface-muted text-sp-text-muted dark:bg-sp-surface-muted dark:text-sp-text-muted',
        default => 'bg-sp-success-soft text-sp-success-foreground dark:bg-sp-success-soft dark:text-sp-success',
    };

    $dot = match ($tone) {
        'success' => 'bg-sp-success dark:bg-sp-success',
        'info' => 'bg-sp-info dark:bg-sp-info',
        'danger' => 'bg-sp-danger dark:bg-sp-danger',
        'warning' => 'bg-sp-warning dark:bg-sp-warning',
        default => 'bg-sp-text-muted dark:bg-sp-text-muted',
    };
@endphp

<span class="inline-flex items-center gap-2 rounded-full px-3.5 py-2 text-xs font-extrabold shadow-sm {{ $classes }}">
    <span class="h-2 w-2 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>