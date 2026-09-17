<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginInactiveUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()
            ->admin()
            ->inactive()
            ->create([
                'email' => 'inactive@stockpilot.test',
            ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logging_out_inactive_user_destroys_the_session(): void
    {
        $user = User::factory()
            ->admin()
            ->inactive()
            ->create([
                'email' => 'stale-session@stockpilot.test',
            ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_active_user_can_still_log_in(): void
    {
        $user = User::factory()
            ->admin()
            ->create([
                'email' => 'active@stockpilot.test',
            ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_session_user_is_redirected_to_login_with_error(): void
    {
        $user = User::factory()
            ->admin()
            ->inactive()
            ->create([
                'email' => 'logged-out@stockpilot.test',
            ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
