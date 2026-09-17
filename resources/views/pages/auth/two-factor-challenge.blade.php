<x-layouts::auth :title="__('Two-factor authentication')">
    <div
        x-data="{
            showRecoveryInput: @js($errors->has('recovery_code')),
            code: '',
            recovery_code: '',

            focusAuthenticationCode() {
                this.$nextTick(() => {
                    this.$refs.otp?.focus();
                });
            },

            focusRecoveryCode() {
                this.$nextTick(() => {
                    this.$refs.recovery_code?.focus();
                });
            },

            init() {
                if (this.showRecoveryInput) {
                    this.focusRecoveryCode();
                } else {
                    this.focusAuthenticationCode();
                }
            },

            toggleInput() {
                this.showRecoveryInput = !this.showRecoveryInput;

                this.code = '';
                this.recovery_code = '';

                if (this.showRecoveryInput) {
                    this.focusRecoveryCode();
                } else {
                    this.focusAuthenticationCode();
                }
            }
        }"
        x-init="init()"
        class="flex flex-col gap-7"
    >
        <div x-show="!showRecoveryInput">
            <x-auth-header
                :title="__('Authentication code')"
                :description="__('Enter the authentication code provided by your authenticator application.')"
            />
        </div>

        <div x-show="showRecoveryInput" x-cloak>
            <x-auth-header
                :title="__('Recovery code')"
                :description="__('Please confirm access to your account by entering one of your emergency recovery codes.')"
            />
        </div>

        @if ($errors->any())
            <x-stockpilot.alert type="danger">
                <p class="font-medium">
                    {{ __('The code could not be verified. Please try again.') }}
                </p>

                <ul class="mt-2 list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-stockpilot.alert>
        @endif

        <form
            method="POST"
            action="{{ route('two-factor.login.store') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <div x-show="!showRecoveryInput">
                <div class="rounded-xl border border-sp-border bg-sp-surface-muted/30 p-5">
                    <x-stockpilot.otp
                        name="code"
                        :length="6"
                        x-ref="otp"
                        x-model="code"
                    />
                </div>
            </div>

            <div x-show="showRecoveryInput" x-cloak>
                <div class="grid gap-1.5">
                    <label
                        for="recovery_code"
                        class="text-sm font-medium text-sp-text"
                    >
                        {{ __('Recovery code') }}
                    </label>

                    <input
                        id="recovery_code"
                        name="recovery_code"
                        type="text"
                        x-ref="recovery_code"
                        x-model="recovery_code"
                        x-bind:required="showRecoveryInput"
                        autocomplete="one-time-code"
                        placeholder="{{ __('Enter your recovery code') }}"
                        class="block w-full rounded-lg border border-sp-border bg-sp-surface px-3.5 py-3 text-sm text-sp-text shadow-sm placeholder:text-sp-text-subtle transition focus:border-sp-primary focus:outline-none focus:ring-2 focus:ring-sp-primary/20 dark:bg-sp-surface dark:text-sp-text"
                    />

                    @error('recovery_code')
                        <p class="text-sm text-sp-danger" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <x-stockpilot.button
                variant="primary"
                type="submit"
                class="w-full"
            >
                {{ __('Continue') }}
            </x-stockpilot.button>

            <div class="text-center text-sm leading-6 text-sp-text-muted">
                <span>{{ __('or you can') }}</span>
                <button
                    type="button"
                    x-on:click="toggleInput()"
                    class="ms-1 font-medium text-sp-primary underline underline-offset-4 transition hover:text-sp-brand-dark focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sp-primary focus-visible:ring-offset-2"
                >
                    <span x-show="!showRecoveryInput">
                        {{ __('login using a recovery code') }}
                    </span>

                    <span x-show="showRecoveryInput" x-cloak>
                        {{ __('login using an authentication code') }}
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-layouts::auth>
