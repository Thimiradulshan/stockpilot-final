@props([
    'xData' => null,
    'escapeAction' => null,
    'maxWidth' => 'max-w-[1500px]',
])

<div class="relative min-h-[calc(100vh-4.75rem)] overflow-hidden bg-sp-background">

    
    <div class="pointer-events-none absolute inset-0 z-0 overflow-hidden" aria-hidden="true">

        <div class="absolute -left-32 top-8 h-[420px] w-[420px] rounded-full bg-sp-primary/10 blur-[90px]"></div>

        <div class="absolute right-[-90px] top-[-80px] h-[430px] w-[430px] rounded-full bg-sp-info/15 blur-[100px]"></div>

        <div class="absolute bottom-[120px] left-[38%] h-[300px] w-[300px] rounded-full bg-sp-accent/10 blur-[90px]"></div>

        <div class="absolute -right-24 top-[28%] h-[420px] w-[420px] rounded-full border-[70px] border-sp-primary/[0.05]"></div>

        <div class="absolute left-[19%] top-[16%] h-2 w-2 rounded-full bg-sp-primary/30"></div>

        <div class="absolute left-[21%] top-[18%] h-1.5 w-1.5 rounded-full bg-sp-accent/40"></div>

        <div class="absolute right-[21%] top-[22%] h-2 w-2 rounded-full bg-sp-info/40"></div>

        <div class="absolute bottom-[22%] right-[17%] h-1.5 w-1.5 rounded-full bg-sp-accent/30"></div>

        <div
            class="absolute inset-0 opacity-[0.18]"
            style="
                background-image:
                    linear-gradient(rgba(1, 91, 99, 0.035) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(1, 91, 99, 0.035) 1px, transparent 1px);
                background-size: 36px 36px;
            "
        ></div>

    </div>

    
    <div
        {{ $attributes->class([$maxWidth, 'relative z-10 mx-auto w-full space-y-5 px-4 py-6 sm:px-6 lg:px-8']) }}
        @if ($xData)
            x-data="{{ $xData }}"
        @endif
        @if ($escapeAction)
            @keydown.escape.window="{{ $escapeAction }}"
        @endif
    >
        {{ $slot }}
    </div>

</div>
