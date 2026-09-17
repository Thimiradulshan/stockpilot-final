@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <h1 class="text-2xl font-bold tracking-tight text-sp-text dark:text-white">
        {{ $title }}
    </h1>

    <p class="mt-2 text-sm leading-6 text-sp-text-muted dark:text-sp-text-muted">
        {{ $description }}
    </p>
</div>
