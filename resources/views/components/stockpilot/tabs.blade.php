@props([
    'items' => [],
    'ariaLabel' => 'Section navigation',
])

<nav
    aria-label="{{ $ariaLabel }}"
    class="overflow-x-auto border-b border-sp-border"
>
    <div class="flex min-w-max items-center gap-1">
        @foreach ($items as $item)
            @php
                $href = $item['href'] ?? '#';
                $label = $item['label'] ?? '';
                $active = $item['active'] ?? false;
            @endphp

            <a
                href="{{ $href }}"
                wire:navigate
                @class([
                    'relative inline-flex items-center gap-2 px-4 py-3 text-sm font-semibold transition-colors duration-150',
                    'text-sp-primary' => $active,
                    'text-sp-text-muted hover:text-sp-text' => ! $active,
                ])
                @if ($active)
                    aria-current="page"
                @endif
            >
                @if (!empty($item['icon']))
                    <x-stockpilot.icon
                        :name="$item['icon']"
                        class="h-4 w-4 shrink-0"
                    />
                @endif

                <span>{{ $label }}</span>

                @if ($active)
                    <span
                        class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-sp-primary"
                        aria-hidden="true"
                    ></span>
                @endif
            </a>
        @endforeach
    </div>
</nav>
