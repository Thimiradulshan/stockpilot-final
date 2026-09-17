@props(['ariaLabel' => null])

<section
    {{ $attributes }}
    @if ($ariaLabel)
        aria-label="{{ $ariaLabel }}"
    @endif
    class="relative overflow-hidden rounded-2xl border border-sp-primary/25 bg-gradient-to-r from-sp-surface-soft via-sp-surface to-sp-info-soft/80 p-4 shadow-md dark:border-sp-primary/20 dark:from-sp-brand-dark dark:via-sp-surface dark:to-sp-info-soft"
>

    <div class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-sp-primary via-sp-info to-sp-accent"></div>

    <div class="relative flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        {{ $slot }}

    </div>

</section>