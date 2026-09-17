<?php

use Laravel\Fortify\Actions\ConfirmTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Locked]
    public bool $requiresConfirmation;

    #[Locked]
    public string $qrCodeSvg = '';

    #[Locked]
    public string $manualSetupKey = '';

    public bool $showVerificationStep = false;

    public bool $setupComplete = false;

    #[Validate('required|string|size:6', onUpdate: false)]
    public string $code = '';


public function mount(bool $requiresConfirmation): void
    {
        $this->requiresConfirmation = $requiresConfirmation;
    }

    #[On('start-two-factor-setup')]
    public function startTwoFactorSetup(): void
    {
        $enableTwoFactorAuthentication = app(EnableTwoFactorAuthentication::class);
        $enableTwoFactorAuthentication(auth()->user());

        $this->loadSetupData();
    }


private function loadSetupData(): void
    {
        $user = auth()->user()?->fresh();

        try {
            if (! $user || ! $user->two_factor_secret) {
                throw new Exception('Two-factor setup secret is not available.');
            }

            $this->qrCodeSvg = $user->twoFactorQrCodeSvg();
            $this->manualSetupKey = decrypt($user->two_factor_secret);
        } catch (Exception) {
            $this->addError('setupData', 'Failed to fetch setup data.');

            $this->reset('qrCodeSvg', 'manualSetupKey');
        }
    }


public function showVerificationIfNecessary(): void
    {
        if ($this->requiresConfirmation) {
            $this->showVerificationStep = true;

            $this->resetErrorBag();

            return;
        }

        $this->closeModal();
        $this->dispatch('two-factor-enabled');
    }


public function confirmTwoFactor(ConfirmTwoFactorAuthentication $confirmTwoFactorAuthentication): void
    {
        $this->validate();

        $confirmTwoFactorAuthentication(auth()->user(), $this->code);

        $this->setupComplete = true;

        $this->closeModal();

        $this->dispatch('two-factor-enabled');
    }


public function resetVerification(): void
    {
        $this->reset('code', 'showVerificationStep');

        $this->resetErrorBag();
    }


public function closeModal(): void
    {
        $this->reset(
            'code',
            'manualSetupKey',
            'qrCodeSvg',
            'showVerificationStep',
            'setupComplete',
        );

        $this->resetErrorBag();

        $this->dispatch('hide-2fa-modal');
    }


#[Computed]
    public function modalConfig(): array
    {
        if ($this->setupComplete) {
            return [
                'title' => __('Two-factor authentication enabled'),
                'description' => __('Two-factor authentication is now enabled. Use your authenticator app to generate sign-in codes.'),
                'buttonText' => __('Close'),
            ];
        }

        if ($this->showVerificationStep) {
            return [
                'title' => __('Verify authentication code'),
                'description' => __('Enter the 6-digit code from your authenticator app.'),
                'buttonText' => __('Continue'),
            ];
        }

        return [
            'title' => __('Enable two-factor authentication'),
            'description' => __('To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app.'),
            'buttonText' => __('Continue'),
        ];
    }
}; ?>

<div
    x-data="{ open: false }"
    x-on:show-2fa-modal.window="open = true"
    x-on:hide-2fa-modal.window="open = false"
    x-on:keydown.escape.window="if (open) { open = false; $wire.closeModal(); }"
>
    <div
        x-cloak
        x-show="open"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[100] flex items-center justify-center bg-sp-brand-dark/60 p-4"
        x-on:click.self="open = false; $wire.closeModal()"
        role="dialog"
        aria-modal="true"
        x-bind:aria-label="$wire.modalConfig.title"
    >
        <div
            class="w-full max-w-md overflow-hidden rounded-2xl border border-sp-border bg-sp-surface shadow-2xl"
            x-show="open"
            x-transition
        >
            <div class="flex items-start justify-between gap-4 border-b border-sp-border px-5 py-4">
                <div class="min-w-0">
                    <h3 class="text-sm font-bold text-sp-brand-dark dark:text-white">
                        {{ $this->modalConfig['title'] }}
                    </h3>

                    <p class="mt-0.5 text-xs leading-5 text-sp-text-muted">
                        {{ $this->modalConfig['description'] }}
                    </p>
                </div>

                <button
                    type="button"
                    x-on:click="open = false; $wire.closeModal()"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-sp-text-muted transition hover:bg-sp-surface-muted hover:text-sp-text"
                    aria-label="{{ __('Close') }}"
                >
                    <x-stockpilot.icon
                        name="x"
                        class="h-4 w-4"
                    />
                </button>
            </div>

            <div class="space-y-6 p-5">
                @if ($showVerificationStep)
                    <div x-data class="space-y-6">
                        <div class="flex justify-center">
                            <input
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                autocomplete="one-time-code"
                                wire:model="code"
                                placeholder="______"
                                x-init="$nextTick(() => $el.focus())"
                                class="w-48 rounded-lg border border-sp-border bg-sp-surface px-4 py-3 text-center font-mono text-lg tracking-[0.5em] text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                            >
                        </div>

                        @error('code')
                            <p class="text-center text-sm font-medium text-sp-danger">
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                wire:click="resetVerification"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm font-semibold text-sp-text transition hover:bg-sp-surface-muted focus:outline-none focus:ring-2 focus:ring-sp-primary/20"
                            >
                                {{ __('Back') }}
                            </button>

                            <button
                                type="button"
                                wire:click="confirmTwoFactor"
                                x-bind:disabled="$wire.code.length < 6"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {{ __('Confirm') }}
                            </button>
                        </div>
                    </div>
                @else
                    @error('setupData')
                        <div class="rounded-xl border border-sp-danger/20 bg-sp-danger/[0.07] px-4 py-3 text-sm font-medium text-sp-danger">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex justify-center">
                        <div
                            class="relative aspect-square w-64 overflow-hidden rounded-lg border border-sp-border bg-sp-surface"
                            x-data
                        >
                            @empty($qrCodeSvg)
                                <div class="absolute inset-0 flex items-center justify-center animate-pulse bg-sp-surface-muted">
                                    <x-stockpilot.icon
                                        name="info"
                                        class="h-6 w-6 text-sp-text-subtle"
                                    />
                                </div>
                            @else
                                <div class="flex h-full items-center justify-center bg-white p-4">
                                    <div
                                        class="bg-white p-3"
                                        :style="$store.theme.dark ? 'filter: invert(1) brightness(1.5)' : ''"
                                    >
                                        {!! $qrCodeSvg !!}
                                    </div>
                                </div>
                            @endempty
                        </div>
                    </div>

                    @if (! $setupComplete)
                        <button
                            type="button"
                            wire:click="showVerificationIfNecessary"
                            x-bind:disabled="$errors.has('setupData')"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            {{ $this->modalConfig['buttonText'] }}
                        </button>
                    @else
                        <button
                            type="button"
                            x-on:click="open = false; $wire.closeModal()"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30"
                        >
                            {{ $this->modalConfig['buttonText'] }}
                        </button>
                    @endif

                    <div class="space-y-4">
                        <div class="relative flex items-center justify-center">
                            <div class="absolute inset-x-0 top-1/2 h-px bg-sp-border"></div>

                            <span class="relative bg-sp-surface px-3 text-xs font-semibold uppercase tracking-wide text-sp-text-muted">
                                {{ __('or, enter the code manually') }}
                            </span>
                        </div>

                        <div
                            class="flex items-stretch overflow-hidden rounded-lg border border-sp-border bg-sp-surface"
                            x-data="{
                                copied: false,
                                async copy() {
                                    try {
                                        await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                        this.copied = true;
                                        setTimeout(() => this.copied = false, 1500);
                                    } catch (e) {
                                        console.warn('Could not copy to clipboard');
                                    }
                                }
                            }"
                        >
                            @empty($manualSetupKey)
                                <div class="flex w-full items-center justify-center bg-sp-surface-muted p-3">
                                    <svg class="h-4 w-4 animate-spin text-sp-text-subtle" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 0 8 8v4C5.373 24 0 18.627 0 12h4Z"></path>
                                    </svg>
                                </div>
                            @else
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $manualSetupKey }}"
                                    class="w-full select-text bg-surface-less px-4 py-3 text-sm font-mono text-sp-text outline-none"
                                >

                                <button
                                    type="button"
                                    x-on:click="copy()"
                                    class="shrink-0 border-s border-sp-border px-4 text-sp-text-muted transition hover:bg-sp-surface-muted hover:text-sp-text"
                                    aria-label="{{ __('Copy setup key') }}"
                                >
                                    <svg
                                        x-show="!copied"
                                        class="h-4 w-4"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect x="9" y="9" width="12" height="12" rx="2"/>
                                        <path d="M5 15V5a2 2 0 0 1 2-2h10"/>
                                    </svg>

                                    <x-stockpilot.icon
                                        x-show="copied"
                                        name="check"
                                        class="h-4 w-4 text-sp-success"
                                    />
                                </button>
                            @endempty
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>