<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Appearance settings')] class extends Component {

}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Appearance settings') }}</h2>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">
        <div class="space-y-6">
            <div>
                <h3 class="text-base font-bold text-sp-brand-dark dark:text-white">
                    {{ __('Theme') }}
                </h3>

                <p class="mt-1 text-sm text-sp-text-muted">
                    {{ __('Choose how StockPilot looks on this device.') }}
                </p>
            </div>

            <div x-data class="grid gap-3 sm:grid-cols-3">
                @foreach ([
                    'light' => [
                        'label' => __('Light'),
                        'description' => __('Always use the light theme.'),
                        'icon' => 'sun',
                    ],
                    'dark' => [
                        'label' => __('Dark'),
                        'description' => __('Always use the dark theme.'),
                        'icon' => 'moon',
                    ],
                    'system' => [
                        'label' => __('System'),
                        'description' => __('Match your device appearance.'),
                        'icon' => 'system',
                    ],
                ] as $themeKey => $theme)
                    <button
                        type="button"
                        x-on:click="$store.theme.set('{{ $themeKey }}')"
                        role="radio"
                        class="relative flex flex-col items-start gap-4 rounded-xl border p-5 text-left outline-none transition focus:ring-2 focus:ring-sp-primary/30"
                        :class="$store.theme.theme === '{{ $themeKey }}' ? 'border-sp-primary bg-sp-primary/[0.04] ring-1 ring-sp-primary/20' : 'border-sp-border bg-sp-surface hover:border-sp-border-strong'"
                        :aria-checked="$store.theme.theme === '{{ $themeKey }}'"
                    >
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
                            :class="$store.theme.theme === '{{ $themeKey }}' ? 'bg-sp-primary text-sp-primary-foreground' : 'bg-sp-surface-muted text-sp-text-muted'"
                        >
                            <x-stockpilot.icon
                                :name="$theme['icon']"
                                class="h-5 w-5"
                            />
                        </span>

                        <span class="min-w-0">
                            <span class="block text-sm font-bold text-sp-text">
                                {{ $theme['label'] }}
                            </span>

                            <span class="mt-0.5 block text-xs leading-5 text-sp-text-muted">
                                {{ $theme['description'] }}
                            </span>
                        </span>

                        <span
                            class="absolute end-4 top-4 flex h-5 w-5 items-center justify-center rounded-full border"
                            :class="$store.theme.theme === '{{ $themeKey }}' ? 'border-sp-primary bg-sp-primary text-sp-primary-foreground' : 'border-sp-border-strong text-transparent'"
                            aria-hidden="true"
                        >
                            <x-stockpilot.icon
                                name="check"
                                class="h-3 w-3"
                            />
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </x-pages::settings.layout>
</section>