<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-7">
        <x-auth-header
            :title="__('Forgot password')"
            :description="__('Enter your email to receive a password reset link')"
        />

        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        @if ($errors->any())
            <x-stockpilot.alert type="danger">
                <p class="font-medium">
                    {{ __('Please check the following and try again.') }}
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
            action="{{ route('password.email') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <x-stockpilot.input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <x-stockpilot.button
                variant="primary"
                type="submit"
                class="w-full"
                data-test="email-password-reset-link-button"
            >
                {{ __('Email password reset link') }}
            </x-stockpilot.button>
        </form>

        <div class="text-center text-sm text-sp-text-muted">
            <span>{{ __('Or, return to') }}</span>
            <x-stockpilot.link
                :href="route('login')"
                wire:navigate
            >
                {{ __('log in') }}
            </x-stockpilot.link>
        </div>
    </div>
</x-layouts::auth>
