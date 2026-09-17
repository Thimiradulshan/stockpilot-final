@props([
    'open',
    'close',
    'title',
    'subtitle' => null,
    'eyebrow' => null,
    'icon' => null,
    'width' => 'max-w-lg',
    'headingId' => null,
    'dialogLabel' => null,
])

<div
    x-show="{{ $open }}"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-50 overflow-y-auto"
    role="dialog"
    aria-modal="true"
    @if ($dialogLabel)
        aria-label="{{ $dialogLabel }}"
    @elseif ($headingId)
        aria-labelledby="{{ $headingId }}"
    @endif
>

    <div
        class="flex min-h-full items-center justify-center bg-sp-brand-dark/50 p-4 backdrop-blur-sm dark:bg-black/70"
        @click.self="{{ $close }}"
    >

        <div
            x-show="{{ $open }}"
            x-transition:enter="transition duration-200 ease-out"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition duration-150 ease-in"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-95 opacity-0"
            class="w-full {{ $width }} overflow-hidden rounded-2xl border border-sp-border bg-sp-surface shadow-2xl dark:border-sp-border dark:bg-sp-surface"
            @click.outside="{{ $close }}"
        >

            <div class="border-b border-sp-border px-6 py-5 dark:border-sp-border">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        @if ($eyebrow)
                            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-sp-primary">

                                @if ($icon)
                                    <x-stockpilot.icon :name="$icon" class="h-4 w-4" />
                                @endif

                                <span>{{ $eyebrow }}</span>

                            </div>
                        @endif

                        <h2
                            @if ($headingId)
                                id="{{ $headingId }}"
                            @endif
                            @class([
                                'text-lg font-bold text-sp-text',
                                'mt-1' => $eyebrow,
                            ])
                        >
                            {{ $title }}
                        </h2>

                        @if ($subtitle)
                            <p class="mt-1 text-sm text-sp-text-subtle">
                                {{ $subtitle }}
                            </p>
                        @endif

                    </div>

                    <button
                        type="button"
                        @click="{{ $close }}"
                        class="rounded-lg p-2 text-sp-text-subtle transition hover:bg-sp-surface-muted hover:text-sp-text dark:text-sp-text-subtle dark:hover:bg-sp-surface-muted dark:hover:text-sp-text"
                        aria-label="{{ __('Close') }}"
                    >
                        <x-stockpilot.icon name="x" class="h-5 w-5" />
                    </button>

                </div>

            </div>

            {{ $slot }}

            @isset($footer)
                <div class="flex flex-col-reverse gap-3 border-t border-sp-border bg-sp-surface-muted px-6 py-4 sm:flex-row sm:justify-end dark:border-sp-border dark:bg-sp-surface-muted">

                    {{ $footer }}

                </div>
            @endisset

        </div>

    </div>

</div>