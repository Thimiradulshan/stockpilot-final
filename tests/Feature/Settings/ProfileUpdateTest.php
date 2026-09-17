<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('My Profile')
            ->assertDontSee('Delete account')
            ->assertDontSee('delete-user');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Updated User')
            ->set('email', 'updated@example.com')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame('Updated User', $user->name);
        $this->assertSame('updated@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertNull($user->profile_photo_path);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $verifiedAt = $user->email_verified_at;

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->set('name', 'Updated User')
            ->set('email', $user->email)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertSame(
            $verifiedAt->toDateTimeString(),
            $user->email_verified_at?->toDateTimeString()
        );
    }

    public function test_profile_photo_can_be_uploaded(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = UploadedFile::fake()->image('profile.jpg', 300, 300);

        Livewire::test('pages::settings.profile')
            ->set('photo', $photo)
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        $this->assertStringStartsWith(
            'profile-photos/',
            $user->profile_photo_path
        );

        Storage::disk('public')->assertExists($user->profile_photo_path);
    }

    public function test_invalid_profile_photo_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user);

        $photo = UploadedFile::fake()->create(
            'profile.txt',
            100,
            'text/plain'
        );

        Livewire::test('pages::settings.profile')
            ->set('photo', $photo)
            ->call('updateProfileInformation')
            ->assertHasErrors(['photo']);

        $user->refresh();

        $this->assertNull($user->profile_photo_path);
    }

    public function test_profile_photo_can_be_removed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'profile_photo_path' => 'profile-photos/existing.jpg',
        ]);

        Storage::disk('public')->put(
            'profile-photos/existing.jpg',
            'fake-image-content'
        );

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->call('removeProfilePhoto')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertNull($user->profile_photo_path);

        Storage::disk('public')->assertMissing(
            'profile-photos/existing.jpg'
        );
    }

    public function test_self_account_deletion_is_not_available(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test('pages::settings.profile')
            ->assertDontSee('Delete account')
            ->assertDontSee('delete-user')
            ->assertDontSee('deleteUser');

        $this->assertNotNull($user->fresh());
        $this->assertAuthenticatedAs($user);
    }
}
