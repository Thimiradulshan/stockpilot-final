<x-layouts::auth :title="__('Confirm password')">
    <div class="flex flex-col gap-7">
        <x-auth-header
            :title="__('Confirm password')"
            :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
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
            action="{{ route('password.confirm.store') }}"
            class="flex flex-col gap-5"
        >
            @csrf

            <x-stockpilot.password
                name="password"
                :label="__('Password')"
                required
                autofocus
                autocomplete="current-password"
                :placeholder="__('Password')"
            />

            <x-stockpilot.button
                variant="primary"
                type="submit"
                class="w-full"
                data-test="confirm-password-button"
            >
                {{ __('Confirm') }}
            </x-stockpilot.button>
        </form>
    </div>
</x-layouts::auth>
