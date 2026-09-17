@props([
    'href',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'font-medium text-sp-primary underline-offset-4 transition hover:text-sp-brand-dark hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary focus-visible:ring-offset-2 focus-visible:ring-offset-sp-background',
    ]) }}
>
    {{ $slot }}
</a>
