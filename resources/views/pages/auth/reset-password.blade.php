<x-layouts::auth :title="__('Reset password')">
    <div class="flex flex-col gap-7">
        <x-auth-header
            :title="__('Reset password')"
            :description="__('Please enter your new password below')"
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
            action="{{ route('password.update') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ request()->route('token') }}"
            >

            <x-stockpilot.input
                name="email"
                :value="request('email')"
                :label="__('Email')"
                type="email"
                required
                autofocus
                autocomplete="email"
            />

            <x-stockpilot.password
                name="password"
                :label="__('Password')"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
            />

            <x-stockpilot.password
                name="password_confirmation"
                :label="__('Confirm password')"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
            />

            <x-stockpilot.button
                type="submit"
                variant="primary"
                class="w-full"
                data-test="reset-password-button"
            >
                {{ __('Reset password') }}
            </x-stockpilot.button>
        </form>
    </div>
</x-layouts::auth>
