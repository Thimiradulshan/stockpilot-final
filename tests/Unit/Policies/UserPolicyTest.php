<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_admin_can_view_users(): void
    {
        $policy = new UserPolicy;

        $this->assertTrue(
            $policy->viewAny(User::factory()->admin()->create())
        );

        $this->assertFalse(
            $policy->viewAny(User::factory()->sales()->create())
        );

        $this->assertFalse(
            $policy->viewAny(User::factory()->stock()->create())
        );

        $this->assertFalse(
            $policy->viewAny(
                User::factory()->admin()->inactive()->create()
            )
        );
    }

    public function test_only_active_admin_can_view_a_user(): void
    {
        $policy = new UserPolicy;

        $admin = User::factory()->admin()->create();
        $target = User::factory()->sales()->create();

        $this->assertTrue($policy->view($admin, $target));

        $this->assertFalse(
            $policy->view(User::factory()->sales()->create(), $target)
        );

        $this->assertFalse(
            $policy->view(User::factory()->stock()->create(), $target)
        );
    }

    public function test_only_active_admin_can_create_users(): void
    {
        $policy = new UserPolicy;

        $this->assertTrue(
            $policy->create(User::factory()->admin()->create())
        );

        $this->assertFalse(
            $policy->create(User::factory()->sales()->create())
        );

        $this->assertFalse(
            $policy->create(User::factory()->stock()->create())
        );

        $this->assertFalse(
            $policy->create(
                User::factory()->admin()->inactive()->create()
            )
        );
    }

    public function test_admin_can_update_another_user(): void
    {
        $policy = new UserPolicy;

        $admin = User::factory()->admin()->create();
        $target = User::factory()->sales()->create();

        $this->assertTrue($policy->update($admin, $target));
    }

    public function test_admin_cannot_update_their_own_user_record(): void
    {
        $policy = new UserPolicy;

        $admin = User::factory()->admin()->create();

        $this->assertFalse($policy->update($admin, $admin));
    }

    public function test_non_admin_cannot_update_users(): void
    {
        $policy = new UserPolicy;

        $sales = User::factory()->sales()->create();
        $stock = User::factory()->stock()->create();
        $target = User::factory()->admin()->create();

        $this->assertFalse($policy->update($sales, $target));
        $this->assertFalse($policy->update($stock, $target));
    }

    public function test_user_deletion_is_disabled(): void
    {
        $policy = new UserPolicy;

        $admin = User::factory()->admin()->create();
        $target = User::factory()->sales()->create();

        $this->assertFalse($policy->delete($admin, $target));
        $this->assertFalse($policy->restore($admin, $target));
        $this->assertFalse($policy->forceDelete($admin, $target));
    }
}
