@props([
    'status',
])

@if ($status)
    <div
        {{ $attributes->merge([
            'class' => 'rounded-lg border border-sp-success/30 bg-sp-success/10 px-4 py-3 text-sm font-medium text-sp-success dark:bg-sp-success/15',
        ]) }}
        role="status"
        aria-live="polite"
    >
        {{ $status }}
    </div>
@endif
