<?php

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Attributes\On;

new #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public string $statusMessage = '';

    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;


public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }

    }


public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->resetValidation();

        $this->statusMessage = __('Password updated.');
    }


#[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }


public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;

        $this->statusMessage = __('Two-factor authentication disabled.');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">{{ __('Security settings') }}</h2>

    <x-pages::settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        @if ($statusMessage !== '')
            <div class="rounded-xl border border-sp-success/20 bg-sp-success/[0.07] px-4 py-3 text-sm font-medium text-sp-success">
                {{ $statusMessage }}
            </div>
        @endif

        <form
            method="POST"
            wire:submit="updatePassword"
            class="mt-6 space-y-6"
        >
            <div>
                <label for="current_password" class="mb-2 block text-sm font-semibold text-sp-text">
                    {{ __('Current password') }}
                </label>

                <input
                    id="current_password"
                    type="password"
                    wire:model="current_password"
                    required
                    autocomplete="current-password"
                    class="block w-full rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                >

                @error('current_password')
                    <p class="mt-2 text-sm font-medium text-sp-danger">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="new_password" class="mb-2 block text-sm font-semibold text-sp-text">
                    {{ __('New password') }}
                </label>

                <input
                    id="new_password"
                    type="password"
                    wire:model="password"
                    required
                    autocomplete="new-password"
                    class="block w-full rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                >

                @error('password')
                    <p class="mt-2 text-sm font-medium text-sp-danger">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="new_password_confirmation" class="mb-2 block text-sm font-semibold text-sp-text">
                    {{ __('Confirm password') }}
                </label>

                <input
                    id="new_password_confirmation"
                    type="password"
                    wire:model="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="block w-full rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                >

                @error('password_confirmation')
                    <p class="mt-2 text-sm font-medium text-sp-danger">
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center gap-4">
                <button
                    type="submit"
                    data-test="update-password-button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-sp-primary px-5 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30 disabled:opacity-60"
                >
                    {{ __('Save') }}
                </button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12 border-t border-sp-border pt-8">
                <h3 class="text-base font-bold text-sp-brand-dark dark:text-white">
                    {{ __('Two-factor authentication') }}
                </h3>

                <p class="mt-1 text-sm text-sp-text-muted">
                    {{ __('Manage your two-factor authentication settings') }}
                </p>

                <div class="mt-6 space-y-6 text-sm" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <p class="leading-6 text-sp-text-muted">
                                {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                            </p>

                            <div class="flex justify-start">
                                <button
                                    type="button"
                                    data-test="disable-2fa-button"
                                    wire:click="disable"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-sp-danger/20 bg-sp-danger/[0.04] px-4 py-2.5 text-sm font-semibold text-sp-danger transition hover:bg-sp-danger/10 focus:outline-none focus:ring-2 focus:ring-sp-danger/20 disabled:opacity-60"
                                >
                                    {{ __('Disable 2FA') }}
                                </button>
                            </div>

                            <livewire:pages::settings.two-factor.recovery-codes :$requiresConfirmation />
                        </div>
                    @else
                        <div class="space-y-4">
                            <p class="leading-6 text-sp-text-muted">
                                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                            </p>

                            <div class="flex justify-start">
                                <button
                                    type="button"
                                    data-test="enable-2fa-button"
                                    wire:click="$dispatch('start-two-factor-setup')"
                                    x-on:click="$dispatch('show-2fa-modal')"
                                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30"
                                >
                                    {{ __('Enable 2FA') }}
                                </button>
                            </div>

                            <livewire:pages::settings.two-factor-setup-modal :requires-confirmation="$requiresConfirmation" />
                        </div>
                    @endif
                </div>
            </section>
        @endif

    </x-pages::settings.layout>

</section>