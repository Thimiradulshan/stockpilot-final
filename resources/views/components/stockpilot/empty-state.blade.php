@props([
    'icon' => 'info',
    'title',
    'message' => null,
])

<div class="mx-auto flex max-w-md flex-col items-center">

    <div class="relative flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-sp-primary/25 to-sp-info-soft text-sp-primary dark:from-sp-brand-dark dark:to-sp-info-soft dark:text-sp-success">

        <span class="absolute -right-1 -top-1 h-5 w-5 rounded-full bg-sp-accent/30"></span>

        <x-stockpilot.icon :name="$icon" class="h-7 w-7" />

    </div>

    <h3 class="mt-5 text-lg font-extrabold text-sp-text">
        {{ $title }}
    </h3>

    @if ($message)
        <p class="mt-2 text-sm leading-6 text-sp-text-muted">
            {{ $message }}
        </p>
    @endif

    @isset($actions)
        <div class="mt-5 flex flex-wrap justify-center gap-2">
            {{ $actions }}
        </div>
    @endisset

</div>