<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('My Profile')] class extends Component {
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $photo = null;

    public string $statusMessage = '';

    public function mount(): void
    {
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
    }


protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }


protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }


public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => $this->nameRules(),
            'email' => $this->emailRules($user->id),
            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($this->photo !== null) {
            $oldPhotoPath = $user->profile_photo_path;

            $newPhotoPath = $this->photo->store(
                'profile-photos',
                'public'
            );

            $user->profile_photo_path = $newPhotoPath;
            $user->save();

            if (
                is_string($oldPhotoPath)
                && $oldPhotoPath !== ''
                && $oldPhotoPath !== $newPhotoPath
            ) {
                Storage::disk('public')->delete($oldPhotoPath);
            }
        } else {
            $user->save();
        }

        $this->photo = null;
        $this->resetValidation();

        $this->statusMessage = __('Profile updated successfully.');
    }


public function removeProfilePhoto(): void
    {
        $user = Auth::user();

        $oldPhotoPath = $user->profile_photo_path;

        if (! is_string($oldPhotoPath) || $oldPhotoPath === '') {
            $this->statusMessage = __('No profile photo is currently set.');

            return;
        }

        $user->profile_photo_path = null;
        $user->save();

        Storage::disk('public')->delete($oldPhotoPath);

        $this->photo = null;
        $this->resetValidation();

        $this->statusMessage = __('Profile photo removed.');
    }
}; ?>

<section class="w-full">
    <div class="relative overflow-hidden rounded-2xl border border-sp-border bg-sp-surface shadow-sm">
        <div
            class="absolute inset-x-0 top-0 h-1 bg-sp-primary"
            aria-hidden="true"
        ></div>


        <div class="bg-sp-info/[0.04] px-5 py-6 sm:px-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-2 flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-sp-text-muted">
                        <span>{{ __('Account') }}</span>
                        <span class="text-sp-text-subtle">/</span>
                        <span class="text-sp-primary">{{ __('My Profile') }}</span>
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-sp-brand-dark dark:text-white">
                        {{ __('My Profile') }}
                    </h1>

                    <p class="mt-1 text-sm leading-6 text-sp-text-muted">
                        {{ __('Manage your personal information and profile picture.') }}
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 self-start rounded-full bg-sp-success/10 px-3 py-1.5 text-xs font-bold text-sp-success">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                    {{ __('Active account') }}
                </div>
            </div>
        </div>


        <div class="border-t border-sp-border bg-sp-surface px-4 sm:px-6">
            <nav
                aria-label="{{ __('Account navigation') }}"
                class="flex items-center gap-1 overflow-x-auto"
            >
                <a
                    href="{{ route('profile.edit') }}"
                    wire:navigate
                    aria-current="page"
                    class="relative inline-flex shrink-0 items-center gap-2 px-4 py-3 text-sm font-semibold text-sp-primary"
                >
                    <x-stockpilot.icon
                        name="user"
                        class="h-4 w-4"
                    />

                    {{ __('Profile') }}

                    <span
                        class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-sp-primary"
                        aria-hidden="true"
                    ></span>
                </a>

                @if (Route::has('security.edit'))
                    <a
                        href="{{ route('security.edit') }}"
                        wire:navigate
                        class="inline-flex shrink-0 items-center gap-2 px-4 py-3 text-sm font-semibold text-sp-text-muted transition hover:text-sp-text"
                    >
                        <x-stockpilot.icon
                            name="settings"
                            class="h-4 w-4"
                        />

                        {{ __('Security') }}
                    </a>
                @endif

                @if (Route::has('appearance.edit'))
                    <a
                        href="{{ route('appearance.edit') }}"
                        wire:navigate
                        class="inline-flex shrink-0 items-center gap-2 px-4 py-3 text-sm font-semibold text-sp-text-muted transition hover:text-sp-text"
                    >
                        <x-stockpilot.icon
                            name="sun"
                            class="h-4 w-4"
                        />

                        {{ __('Appearance') }}
                    </a>
                @endif
            </nav>
        </div>


        <div class="p-5 sm:p-7">
            @if ($statusMessage !== '')
                <div class="mb-6 rounded-xl border border-sp-success/20 bg-sp-success/[0.07] px-4 py-3 text-sm font-medium text-sp-success">
                    {{ $statusMessage }}
                </div>
            @endif

            <form
                wire:submit="updateProfileInformation"
                class="space-y-8"
            >

                <section>
                    <div class="mb-4">
                        <h2 class="text-base font-bold text-sp-brand-dark dark:text-white">
                            {{ __('Profile picture') }}
                        </h2>

                        <p class="mt-1 text-sm text-sp-text-muted">
                            {{ __('Use a clear photo or keep your initials as the default avatar.') }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-5">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">


                            <div class="shrink-0">
                                @if ($photo && $photo->isPreviewable())
                                    <div class="relative h-24 w-24 overflow-hidden rounded-full border-4 border-white bg-sp-info/10 shadow-md dark:border-sp-surface">
                                        <img
                                            src="{{ $photo->temporaryUrl() }}"
                                            alt="{{ __('Selected profile photo preview') }}"
                                            class="h-full w-full object-cover"
                                        >
                                    </div>
                                @elseif (Auth::user()->profilePhotoUrl())
                                    <div class="relative h-24 w-24 overflow-hidden rounded-full border-4 border-white bg-sp-info/10 shadow-md dark:border-sp-surface">
                                        <img
                                            src="{{ Auth::user()->profilePhotoUrl() }}"
                                            alt="{{ __('Profile photo') }}"
                                            class="h-full w-full object-cover"
                                        >
                                    </div>
                                @else
                                    <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-sp-primary text-xl font-bold text-white shadow-md dark:border-sp-surface">
                                        {{ Auth::user()->initials() }}
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap gap-2">
                                    <label
                                        for="profile-photo"
                                        class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-sp-primary px-4 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:brightness-95 focus-within:ring-2 focus-within:ring-sp-primary/30"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            class="h-4 w-4"
                                            aria-hidden="true"
                                        >
                                            <path d="M12 5v14"/>
                                            <path d="M5 12h14"/>
                                        </svg>

                                        {{ __('Choose photo') }}
                                    </label>

                                    <input
                                        id="profile-photo"
                                        type="file"
                                        wire:model="photo"
                                        accept="image/jpeg,image/png,image/webp"
                                        class="sr-only"
                                    >

                                    @if (Auth::user()->hasProfilePhoto())
                                        <button
                                            type="button"
                                            wire:click="removeProfilePhoto"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-2 rounded-lg border border-sp-danger/20 bg-sp-danger/[0.04] px-4 py-2.5 text-sm font-semibold text-sp-danger transition hover:bg-sp-danger/10 focus:outline-none focus:ring-2 focus:ring-sp-danger/20 disabled:opacity-60"
                                        >
                                            {{ __('Remove photo') }}
                                        </button>
                                    @endif
                                </div>

                                <p class="mt-3 text-xs leading-5 text-sp-text-muted">
                                    {{ __('JPEG, PNG, or WebP. Maximum file size: 2 MB.') }}
                                </p>

                                <div
                                    wire:loading
                                    wire:target="photo"
                                    class="mt-2 text-xs font-semibold text-sp-primary"
                                >
                                    {{ __('Preparing photo...') }}
                                </div>

                                @error('photo')
                                    <p class="mt-2 text-sm font-medium text-sp-danger">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </section>


                <section class="border-t border-sp-border pt-8">
                    <div class="mb-5">
                        <h2 class="text-base font-bold text-sp-brand-dark dark:text-white">
                            {{ __('Personal information') }}
                        </h2>

                        <p class="mt-1 text-sm text-sp-text-muted">
                            {{ __('Keep your account information up to date.') }}
                        </p>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label
                                for="profile-name"
                                class="mb-2 block text-sm font-semibold text-sp-text"
                            >
                                {{ __('Name') }}
                            </label>

                            <input
                                id="profile-name"
                                type="text"
                                wire:model="name"
                                autocomplete="name"
                                required
                                class="block w-full rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                            >

                            @error('name')
                                <p class="mt-2 text-sm font-medium text-sp-danger">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="profile-email"
                                class="mb-2 block text-sm font-semibold text-sp-text"
                            >
                                {{ __('Email') }}
                            </label>

                            <input
                                id="profile-email"
                                type="email"
                                wire:model="email"
                                autocomplete="email"
                                required
                                class="block w-full rounded-lg border border-sp-border bg-sp-surface px-4 py-2.5 text-sm text-sp-text shadow-sm outline-none transition placeholder:text-sp-text-subtle focus:border-sp-primary focus:ring-2 focus:ring-sp-primary/20"
                            >

                            @error('email')
                                <p class="mt-2 text-sm font-medium text-sp-danger">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </section>


                <section class="border-t border-sp-border pt-8">
                    <div class="mb-5">
                        <h2 class="text-base font-bold text-sp-brand-dark dark:text-white">
                            {{ __('Account information') }}
                        </h2>

                        <p class="mt-1 text-sm text-sp-text-muted">
                            {{ __('Your access level is controlled by StockPilot administration.') }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-sp-text-muted">
                                {{ __('Role') }}
                            </p>

                            <p class="mt-2 text-sm font-bold text-sp-brand-dark dark:text-white">
                                {{ Auth::user()->roleEnum()?->label() ?? __('User') }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-sp-border bg-sp-surface-muted/50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-sp-text-muted">
                                {{ __('Account status') }}
                            </p>

                            @if (Auth::user()->isActive())
                                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-sp-success/10 px-2.5 py-1 text-xs font-bold text-sp-success">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ __('Active') }}
                                </p>
                            @else
                                <p class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-sp-danger/10 px-2.5 py-1 text-xs font-bold text-sp-danger">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ __('Inactive') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </section>


                <div class="flex flex-col-reverse gap-3 border-t border-sp-border pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-sp-text-muted">
                        {{ __('Changes to your email may require verification again.') }}
                    </p>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="updateProfileInformation"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-sp-primary px-5 py-2.5 text-sm font-semibold text-sp-primary-foreground shadow-sm transition hover:-translate-y-0.5 hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-sp-primary/30 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <svg
                            wire:loading
                            wire:target="updateProfileInformation"
                            class="h-4 w-4 animate-spin"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>

                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 0 0 8 8v4C5.373 24 0 18.627 0 12h4Z"
                            ></path>
                        </svg>

                        {{ __('Save changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>


