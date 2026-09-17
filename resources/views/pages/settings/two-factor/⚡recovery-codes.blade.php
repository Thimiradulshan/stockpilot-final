<?php

use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public array $recoveryCodes = [];


public function mount(): void
    {
        $this->loadRecoveryCodes();
    }


public function regenerateRecoveryCodes(GenerateNewRecoveryCodes $generateNewRecoveryCodes): void
    {
        $generateNewRecoveryCodes(auth()->user());

        $this->loadRecoveryCodes();
    }


private function loadRecoveryCodes(): void
    {
        $user = auth()->user();

        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            try {
                $this->recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);
            } catch (Exception) {
                $this->addError('recoveryCodes', 'Failed to load recovery codes');

                $this->recoveryCodes = [];
            }
        }
    }
}; ?>

<div
    class="rounded-xl border border-sp-border bg-sp-surface p-6 shadow-sm"
    wire:cloak
    x-data="{ showRecoveryCodes: false }"
>
    <div class="flex items-start gap-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sp-surface-muted text-sp-text-muted">
            <svg
                class="h-4 w-4"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
            >
                <rect x="4" y="11" width="16" height="9" rx="2"/>
                <path d="M8 11V8a4 4 0 0 1 8 0v3"/>
            </svg>
        </span>

        <div class="min-w-0">
            <h4 class="text-sm font-bold text-sp-brand-dark dark:text-white">
                {{ __('2FA recovery codes') }}
            </h4>

            <p class="mt-1 text-sm leading-6 text-sp-text-muted">
                {{ __('Recovery codes let you regain access if you lose your 2FA device. Store them in a secure password manager.') }}
            </p>
        </div>
    </div>

    <div class="mt-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <button
                type="button"
                x-show="!showRecoveryCodes"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30"
                x-on:click="showRecoveryCodes = true;"
                aria-expanded="false"
                aria-controls="recovery-codes-section"
            >
                <x-stockpilot.icon
                    name="eye"
                    class="h-4 w-4"
                />

                {{ __('View recovery codes') }}
            </button>

            <button
                type="button"
                x-show="showRecoveryCodes"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30"
                x-on:click="showRecoveryCodes = false"
                aria-expanded="true"
                aria-controls="recovery-codes-section"
            >
                {{ __('Hide recovery codes') }}
            </button>

            @if (filled($recoveryCodes))
                <button
                    type="button"
                    x-show="showRecoveryCodes"
                    wire:click="regenerateRecoveryCodes"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted focus:outline-none focus:ring-2 focus:ring-sp-primary/20 disabled:opacity-60"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M3 12a9 9 0 0 1 15.5-6.4L21 8"/>
                        <path d="M21 3v5h-5"/>
                        <path d="M21 12a9 9 0 0 1-15.5 6.4L3 16"/>
                        <path d="M3 21v-5h5"/>
                    </svg>

                    {{ __('Regenerate codes') }}
                </button>
            @endif
        </div>

        <div
            x-show="showRecoveryCodes"
            x-transition
            id="recovery-codes-section"
            class="relative overflow-hidden"
            x-bind:aria-hidden="!showRecoveryCodes"
        >
            <div class="mt-3 space-y-3">
                @error('recoveryCodes')
                    <div class="rounded-xl border border-sp-danger/20 bg-sp-danger/[0.07] px-4 py-3 text-sm font-medium text-sp-danger">
                        {{ $message }}
                    </div>
                @enderror

                @if (filled($recoveryCodes))
                    <div
                        class="grid gap-1 rounded-lg bg-sp-surface-muted p-4 font-mono text-sm text-sp-text"
                        role="list"
                        aria-label="{{ __('Recovery codes') }}"
                    >
                        @foreach ($recoveryCodes as $code)
                            <div
                                role="listitem"
                                class="select-text"
                                wire:loading.class="opacity-50 animate-pulse"
                            >
                                {{ $code }}
                            </div>
                        @endforeach
                    </div>

                    <p class="text-xs leading-5 text-sp-text-muted">
                        {{ __('Each recovery code can be used once to access your account and will be removed after use. If you need more, click Regenerate codes above.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>