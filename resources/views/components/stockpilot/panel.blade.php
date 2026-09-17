@props([
    'eyebrow' => null,
    'title' => null,
    'icon' => null,
    'tone' => 'primary',
])

@php
    $tileTones = [
        'primary' => 'bg-sp-primary/10 text-sp-primary dark:bg-sp-brand-dark dark:text-sp-success',
        'success' => 'bg-sp-success-soft text-sp-success dark:bg-sp-success-soft dark:text-sp-success',
        'info' => 'bg-sp-info-soft text-sp-info-foreground dark:bg-sp-info-soft dark:text-sp-info',
        'accent' => 'bg-sp-accent/15 text-sp-accent dark:bg-sp-accent/15 dark:text-sp-accent',
        'warning' => 'bg-sp-warning-soft text-sp-warning-foreground dark:bg-sp-warning-soft dark:text-sp-warning',
        'danger' => 'bg-sp-danger-soft text-sp-danger dark:bg-sp-danger-soft dark:text-sp-danger',
    ];

    $tileClass = $tileTones[$tone] ?? $tileTones['primary'];
@endphp

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-sp-border-strong/60 bg-sp-surface p-5 shadow-sm dark:border-sp-border-strong dark:bg-sp-surface']) }}>

    @if ($eyebrow || $title || $icon || ($actions ?? null)?->isNotEmpty())
        <header class="flex flex-wrap items-center justify-between gap-4">

            <div class="flex min-w-0 items-center gap-3">

                @if ($icon)
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tileClass }}">
                        <x-stockpilot.icon :name="$icon" class="h-5 w-5" />
                    </div>
                @endif

                <div class="min-w-0">

                    @if ($eyebrow)
                        <p class="text-[11px] font-extrabold uppercase tracking-[0.08em] text-sp-text-muted">
                            {{ $eyebrow }}
                        </p>
                    @endif

                    @if ($title)
                        <h3 class="truncate text-base font-extrabold text-sp-text dark:text-sp-text">
                            {{ $title }}
                        </h3>
                    @endif

                </div>

            </div>

            @if (($actions ?? null)?->isNotEmpty())
                <div class="flex shrink-0 items-center gap-2">
                    {{ $actions }}
                </div>
            @endif

        </header>
    @endif

    <div @class(['mt-5' => $eyebrow || $title || $icon])>
        {{ $slot }}
    </div>

</section>