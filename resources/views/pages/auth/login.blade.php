<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-7">
        <x-auth-header
            :title="__('Log in to your account')"
            :description="__('Enter your email and password below to log in')"
        />

        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        @if ($errors->any())
            <x-stockpilot.alert
                type="danger"
                class="text-start"
            >
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
            action="{{ route('login.store') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <x-stockpilot.input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div class="relative">
                <x-stockpilot.password
                    name="password"
                    :label="__('Password')"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                />

                @if (Route::has('password.request'))
                    <x-stockpilot.link
                        :href="route('password.request')"
                        wire:navigate
                        class="absolute end-0 top-0 text-sm"
                    >
                        {{ __('Forgot your password?') }}
                    </x-stockpilot.link>
                @endif
            </div>

            <x-stockpilot.checkbox
                name="remember"
                :label="__('Remember me')"
                :checked="old('remember')"
            />

            <x-stockpilot.button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="login-button"
            >
                {{ __('Log in') }}
            </x-stockpilot.button>
        </form>
    </div>
</x-layouts::auth>
