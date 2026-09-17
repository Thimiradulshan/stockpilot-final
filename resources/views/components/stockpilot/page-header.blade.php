@props([
    'sectionLabel' => null,
    'sectionCurrent' => null,
    'title',
    'subtitle' => null,
    'description' => null,
    'tags' => [],
])

<section class="relative overflow-hidden rounded-2xl border border-sp-primary/20 bg-gradient-to-r from-sp-surface-soft via-sp-surface to-sp-info-soft/70 shadow-lg dark:border-sp-primary/20 dark:from-sp-brand-dark dark:via-sp-surface dark:to-sp-info-soft">

    
    <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-sp-primary/15 blur-3xl"></div>

    <div class="pointer-events-none absolute right-[18%] bottom-[-70px] h-40 w-40 rounded-full bg-sp-accent/15 blur-3xl"></div>

    <div class="pointer-events-none absolute -left-10 bottom-[-80px] h-48 w-48 rounded-full bg-sp-info/15 blur-3xl"></div>

    
    <div class="absolute inset-y-0 start-0 w-1.5 bg-gradient-to-b from-sp-primary via-sp-success to-sp-accent"></div>

    <div class="relative px-6 py-7 sm:px-8 lg:px-9 lg:py-8">

        <div class="flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">

            <div class="min-w-0">

                @if ($sectionLabel || $sectionCurrent)
                    <div class="mb-3 flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-sp-text-muted">

                        <span class="h-2.5 w-2.5 rounded-full bg-sp-primary dark:bg-sp-success"></span>

                        @if ($sectionLabel)
                            <span>{{ $sectionLabel }}</span>

                            <span class="text-sp-text-subtle">
                                /
                            </span>
                        @endif

                        @if ($sectionCurrent)
                            <span class="text-sp-primary dark:text-sp-success">{{ $sectionCurrent }}</span>
                        @endif

                    </div>
                @endif

                <h1 class="text-[34px] font-extrabold tracking-tight text-sp-text">
                    {{ $title }}
                </h1>

                @if ($subtitle)
                    <p class="mt-2 text-[15px] font-bold text-sp-text-muted">
                        {{ $subtitle }}
                    </p>
                @endif

                @if ($description)
                    <p class="mt-1.5 max-w-2xl text-sm leading-6 text-sp-text-muted">
                        {{ $description }}
                    </p>
                @endif

                @if (! empty($tags))
                    <div class="mt-5 flex flex-wrap gap-2">
                        @foreach ($tags as $tag)
                            @php
                                $tagTone = $tag['tone'] ?? 'primary';

                                $tagClasses = match ($tagTone) {
                                    'success' => 'bg-sp-success-soft text-sp-success-foreground dark:bg-sp-success-soft dark:text-sp-success',
                                    'info' => 'bg-sp-info-soft text-sp-info-foreground dark:bg-sp-info-soft dark:text-sp-info',
                                    'accent' => 'bg-sp-accent/15 text-sp-accent dark:bg-sp-accent/15 dark:text-sp-accent',
                                    'warning' => 'bg-sp-warning-soft text-sp-warning-foreground dark:bg-sp-warning-soft dark:text-sp-warning',
                                    'danger' => 'bg-sp-danger-soft text-sp-danger dark:bg-sp-danger-soft dark:text-sp-danger',
                                    default => 'bg-sp-primary text-sp-primary-foreground dark:bg-sp-primary dark:text-sp-primary-foreground',
                                };

                                $tagDot = match ($tagTone) {
                                    'success' => 'bg-sp-success dark:bg-sp-success',
                                    'info' => 'bg-sp-info dark:bg-sp-info',
                                    'accent' => 'bg-sp-accent dark:bg-sp-accent',
                                    'warning' => 'bg-sp-warning dark:bg-sp-warning',
                                    'danger' => 'bg-sp-danger dark:bg-sp-danger',
                                    default => 'bg-white dark:bg-sp-primary-foreground',
                                };
                            @endphp

                            <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-bold shadow-sm {{ $tagClasses }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $tagDot }}"></span>
                                {{ $tag['label'] }}
                            </span>
                        @endforeach
                    </div>
                @endif

            </div>

            @isset($actions)
                <div class="shrink-0">
                    {{ $actions }}
                </div>
            @endisset

        </div>

    </div>

</section>
