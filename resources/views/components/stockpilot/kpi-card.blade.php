@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'tone' => 'primary',
    'action' => null,
    'href' => null,
    'active' => false,
])

@php
    $palette = [
        'primary' => [
            'border' => 'border-sp-primary/30',
            'gradient' => 'from-sp-primary/20 via-sp-surface to-sp-surface',
            'rail' => 'from-sp-primary to-sp-success',
            'orbA' => 'bg-sp-primary/20',
            'orbB' => 'bg-sp-success/10',
            'dot' => 'bg-sp-primary dark:bg-sp-success',
            'value' => 'text-sp-primary dark:text-sp-success',
            'hint' => 'dark:text-sp-text-subtle',
            'tile' => 'bg-sp-primary text-white shadow-xl shadow-sp-primary/30',
        ],
        'success' => [
            'border' => 'border-sp-success/30',
            'gradient' => 'from-sp-success/15 via-sp-surface to-sp-surface',
            'rail' => 'from-sp-success to-sp-warning',
            'orbA' => 'bg-sp-success/20',
            'orbB' => 'bg-sp-accent/10',
            'dot' => 'bg-sp-success dark:bg-sp-success',
            'value' => 'text-sp-success dark:text-sp-success',
            'hint' => 'dark:text-sp-text-subtle',
            'tile' => 'bg-sp-success text-white shadow-xl shadow-sp-success/30',
        ],
        'info' => [
            'border' => 'border-sp-info/40',
            'gradient' => 'from-sp-info/15 via-sp-surface to-sp-surface',
            'rail' => 'from-sp-info to-sp-accent',
            'orbA' => 'bg-sp-info/25',
            'orbB' => 'bg-sp-accent/10',
            'dot' => 'bg-sp-info dark:bg-sp-info',
            'value' => 'text-sp-info dark:text-sp-info',
            'hint' => 'dark:text-sp-text-subtle',
            'tile' => 'bg-sp-info text-white shadow-xl shadow-sp-info/30',
        ],
        'accent' => [
            'border' => 'border-sp-accent/30',
            'gradient' => 'from-sp-accent/15 via-sp-surface to-sp-surface',
            'rail' => 'from-sp-accent to-sp-danger',
            'orbA' => 'bg-sp-accent/20',
            'orbB' => 'bg-sp-danger/10',
            'dot' => 'bg-sp-accent dark:bg-sp-accent',
            'value' => 'text-sp-accent dark:text-sp-accent',
            'hint' => 'dark:text-sp-text-subtle',
            'tile' => 'bg-sp-accent text-white shadow-xl shadow-sp-accent/30',
        ],
        'warning' => [
            'border' => 'border-sp-warning/30',
            'gradient' => 'from-sp-warning/15 via-sp-surface to-sp-surface',
            'rail' => 'from-sp-warning to-sp-accent',
            'orbA' => 'bg-sp-warning/20',
            'orbB' => 'bg-sp-accent/10',
            'dot' => 'bg-sp-warning dark:bg-sp-warning',
            'value' => 'text-sp-warning-foreground dark:text-sp-warning',
            'hint' => 'dark:text-sp-text-subtle',
            'tile' => 'bg-sp-warning text-white shadow-xl shadow-sp-warning/30',
        ],
        'danger' => [
            'border' => 'border-sp-danger/30',
            'gradient' => 'from-sp-danger/15 via-sp-surface to-sp-surface',
            'rail' => 'from-sp-danger to-sp-accent',
            'orbA' => 'bg-sp-danger/20',
            'orbB' => 'bg-sp-accent/10',
            'dot' => 'bg-sp-danger dark:bg-sp-danger',
            'value' => 'text-sp-danger dark:text-sp-danger',
            'hint' => 'dark:text-sp-text-subtle',
            'tile' => 'bg-sp-danger text-white shadow-xl shadow-sp-danger/30',
        ],
    ];

    $toneClasses = $palette[$tone] ?? $palette['primary'];

    $baseClasses = [
        'group relative overflow-hidden rounded-2xl border p-5 shadow-lg shadow-sp-brand-dark/10 transition duration-200 hover:-translate-y-1 hover:shadow-2xl hover:shadow-sp-brand-dark/15',
        $toneClasses['border'],
        'bg-gradient-to-br',
        $toneClasses['gradient'],
    ];

    if ($action || $href) {
        $baseClasses[] = 'cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary/50 focus-visible:ring-offset-2 focus-visible:ring-offset-sp-background';
    }

    if ($active) {
        $baseClasses[] = 'ring-2 ring-sp-primary/50 ring-offset-2 ring-offset-sp-background';
    }

    $cardClasses = implode(' ', $baseClasses);

    $body = fn (): string => (string) view('components.stockpilot._kpi-card-body', [
        'label' => $label,
        'value' => $value,
        'hint' => $hint,
        'icon' => $icon,
        'toneClasses' => $toneClasses,
    ])->render();
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        wire:navigate
        aria-label="{{ $label }}"
        class="{{ $cardClasses }} block"
    >
        {!! $body() !!}
    </a>
@elseif ($action)
    <button
        type="button"
        wire:click="{{ $action }}"
        aria-label="{{ $label }}"
        class="{{ $cardClasses }} block w-full text-start"
    >
        {!! $body() !!}
    </button>
@else
    <article
        aria-label="{{ $label }}"
        class="{{ $cardClasses }}"
    >
        {!! $body() !!}
    </article>
@endif