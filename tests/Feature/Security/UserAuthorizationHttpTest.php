<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthorizationHttpTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_administration(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_user_administration(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Team management');
    }

    public function test_sales_user_cannot_access_user_administration(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_stock_user_cannot_access_user_administration(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_is_logged_out_of_user_administration(): void
    {
        $admin = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
