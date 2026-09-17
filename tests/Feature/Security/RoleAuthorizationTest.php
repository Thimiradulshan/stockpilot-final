<?php

namespace Tests\Feature\Security;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_user_administration(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_sales_area(): void
    {
        $this->get(route('admin.sales.index'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_products_page(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_access_user_administration(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_sales_user_cannot_access_user_administration(): void
    {
        $user = User::factory()->sales()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_stock_user_cannot_access_user_administration(): void
    {
        $user = User::factory()->stock()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_sales_user_can_access_sales_area(): void
    {
        $user = User::factory()->sales()->create();

        $this->actingAs($user)
            ->get(route('admin.sales.index'))
            ->assertOk();
    }

    public function test_admin_can_access_sales_area(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.sales.index'))
            ->assertOk();
    }

    public function test_stock_user_cannot_access_sales_area(): void
    {
        $user = User::factory()->stock()->create();

        $this->actingAs($user)
            ->get(route('admin.sales.index'))
            ->assertForbidden();
    }

    public function test_stock_user_can_access_products_page(): void
    {
        $user = User::factory()->stock()->create();

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_admin_can_access_products_page(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertOk();
    }

    public function test_sales_user_cannot_access_products_page(): void
    {
        $user = User::factory()->sales()->create();

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_is_logged_out_of_user_administration(): void
    {
        $user = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_sales_user_is_logged_out_of_sales_area(): void
    {
        $user = User::factory()
            ->sales()
            ->inactive()
            ->create();

        $this->actingAs($user)
            ->get(route('admin.sales.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_inactive_stock_user_is_logged_out_of_products_page(): void
    {
        $user = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $this->actingAs($user)
            ->get(route('admin.products.index'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_invalid_role_does_not_grant_access(): void
    {
        $user = User::factory()->create();

        $user->forceFill([
            'role' => 'superadmin',
        ])->save();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_user_role_can_be_resolved_as_a_user_role_enum(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertSame(UserRole::ADMIN, $user->roleEnum());
    }
}
