@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'tone' => 'primary',
    'quiet' => false,
    'href' => null,
])

@php
    $palette = [
        'primary' => [
            'tile' => 'bg-sp-primary/10 text-sp-primary dark:bg-sp-brand-dark dark:text-sp-success',
            'value' => 'text-sp-primary dark:text-sp-success',
            'rail' => 'bg-sp-primary dark:bg-sp-success',
        ],
        'success' => [
            'tile' => 'bg-sp-success-soft text-sp-success dark:bg-sp-success-soft dark:text-sp-success',
            'value' => 'text-sp-success dark:text-sp-success',
            'rail' => 'bg-sp-success dark:bg-sp-success',
        ],
        'info' => [
            'tile' => 'bg-sp-info-soft text-sp-info-foreground dark:bg-sp-info-soft dark:text-sp-info',
            'value' => 'text-sp-info dark:text-sp-info',
            'rail' => 'bg-sp-info dark:bg-sp-info',
        ],
        'accent' => [
            'tile' => 'bg-sp-accent/15 text-sp-accent dark:bg-sp-accent/15 dark:text-sp-accent',
            'value' => 'text-sp-accent dark:text-sp-accent',
            'rail' => 'bg-sp-accent dark:bg-sp-accent',
        ],
        'warning' => [
            'tile' => 'bg-sp-warning-soft text-sp-warning-foreground dark:bg-sp-warning-soft dark:text-sp-warning',
            'value' => 'text-sp-warning-foreground dark:text-sp-warning',
            'rail' => 'bg-sp-warning dark:bg-sp-warning',
        ],
        'danger' => [
            'tile' => 'bg-sp-danger-soft text-sp-danger dark:bg-sp-danger-soft dark:text-sp-danger',
            'value' => 'text-sp-danger dark:text-sp-danger',
            'rail' => 'bg-sp-danger dark:bg-sp-danger',
        ],
    ];

    $toneClasses = $palette[$tone] ?? $palette['primary'];

    if ($quiet) {
        $toneClasses = [
            'tile' => 'bg-sp-surface-muted text-sp-text-muted dark:bg-sp-surface-muted dark:text-sp-text-muted',
            'value' => 'text-sp-text-muted dark:text-sp-text-muted',
            'rail' => 'bg-sp-border-strong dark:bg-sp-border-strong',
        ];
    }

    $baseClasses = [
        'group relative overflow-hidden rounded-2xl border border-sp-border-strong/60 bg-sp-surface p-5 shadow-sm transition duration-200',
        'hover:-translate-y-0.5 hover:shadow-lg hover:shadow-sp-brand-dark/10' => $href !== null,
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary/50 focus-visible:ring-offset-2 focus-visible:ring-offset-sp-background' => $href !== null,
    ];

    $body = fn (): string => (string) view(
        'components.stockpilot._mini-kpi-body',
        [
            'label' => $label,
            'value' => $value,
            'hint' => $hint,
            'icon' => $icon,
            'toneClasses' => $toneClasses,
        ],
    )->render();
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        wire:navigate
        aria-label="{{ $label }}"
        class="{{ collect([...(array) $baseClasses])->filter(fn ($c) => is_string($c))->implode(' ') }} block"
    >
        {!! $body() !!}
    </a>
@else
    <article aria-label="{{ $label }}" class="rounded-2xl {{ collect([...(array) $baseClasses])->filter(fn ($c) => is_string($c))->implode(' ') }}">
        {!! $body() !!}
    </article>
@endif