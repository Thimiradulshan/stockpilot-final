<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_response_sends_expected_nosniff_header(): void
    {
        $response = $this->get(route('login'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_authenticated_responses_send_nosniff_header(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }
}
