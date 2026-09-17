@props([
    'paginator',
    'itemLabel' => 'items',
])

@if ($paginator->hasPages())
    <div class="rounded-2xl border border-sp-border bg-sp-surface px-4 py-3 shadow-sm dark:border-sp-border dark:bg-sp-surface sm:px-6">

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-xs text-sp-text-subtle">
                {{ __('Showing') }}

                <span class="font-bold text-sp-text">
                    {{ $paginator->firstItem() }}
                </span>

                {{ __('to') }}

                <span class="font-bold text-sp-text">
                    {{ $paginator->lastItem() }}
                </span>

                {{ __('of') }}

                <span class="font-bold text-sp-text">
                    {{ $paginator->total() }}
                </span>

                {{ $itemLabel }}
            </p>

            <div>
                {{ $paginator->links() }}
            </div>

        </div>

    </div>
@endif