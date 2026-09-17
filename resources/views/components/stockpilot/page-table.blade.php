@props([
    'mobile' => 'md',
    'ariaLabel' => null,
])

<div
    {{ $attributes }}
    @if ($ariaLabel)
        aria-label="{{ $ariaLabel }}"
    @endif
    @class([
        'overflow-hidden rounded-2xl border border-sp-primary/20 bg-sp-surface shadow-lg dark:border-sp-primary/15 dark:bg-sp-surface',
        'hidden md:block' => $mobile === 'md',
        'hidden lg:block' => $mobile === 'lg',
    ])
>

    
    <div class="h-1.5 bg-gradient-to-r from-sp-primary via-sp-success via-sp-info to-sp-accent"></div>

    <div class="overflow-x-auto">
        {{ $slot }}
    </div>

    @isset($footer)
        {{ $footer }}
    @endisset

</div>
