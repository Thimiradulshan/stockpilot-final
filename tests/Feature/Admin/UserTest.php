<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_active_sales_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Nimal Perera',
                'email' => 'nimal@example.test',
                'role' => 'sales',
                'status' => 'active',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()
            ->where('email', 'nimal@example.test')
            ->firstOrFail();

        $this->assertSame('Nimal Perera', $user->name);
        $this->assertSame('sales', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue($user->isSalesUser());
        $this->assertTrue(Hash::check('secret123', $user->password));
    }

    public function test_admin_can_create_a_stock_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Kamal Silva',
                'email' => 'kamal@example.test',
                'role' => 'stock',
                'status' => 'active',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::query()
            ->where('email', 'kamal@example.test')
            ->firstOrFail();

        $this->assertTrue($user->isStockUser());
    }

    public function test_duplicate_email_is_rejected_on_create(): void
    {
        $admin = User::factory()->admin()->create();
        $existing = User::factory()->create([
            'email' => 'taken@example.test',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Duplicate',
                'email' => $existing->email,
                'role' => 'sales',
                'status' => 'active',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertSessionHasErrors(['email']);
    }

    public function test_password_is_required_on_create(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'No Password',
                'email' => 'nopassword@example.test',
                'role' => 'sales',
                'status' => 'active',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertSessionHasErrors(['password']);
    }

    public function test_admin_can_update_a_user_and_change_role_status(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->sales()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update', $target), [
                'name' => 'Renamed User',
                'email' => $target->email,
                'role' => 'stock',
                'status' => 'inactive',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.users.index'));

        $target->refresh();

        $this->assertSame('Renamed User', $target->name);
        $this->assertSame('stock', $target->role);
        $this->assertSame('inactive', $target->status);
        $this->assertTrue($target->isStockUser());
        $this->assertFalse($target->isActive());
        $this->assertEquals(1, User::query()->where('status', 'inactive')->count());
    }

    public function test_admin_can_update_password_with_same_email(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->sales()->create();

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'sales',
                'status' => 'active',
                'password' => 'newsecret123',
                'password_confirmation' => 'newsecret123',
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertTrue(Hash::check('newsecret123', $target->refresh()->password));
    }

    public function test_blank_password_keeps_current_password(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->sales()->create();
        $originalHash = $target->password;

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'sales',
                'status' => 'active',
                'password' => null,
                'password_confirmation' => null,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertSame($originalHash, $target->refresh()->password);
    }

    public function test_admin_cannot_modify_own_account_through_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'name' => 'Renamed Self',
                'email' => $admin->email,
                'role' => 'admin',
                'status' => 'active',
                'password' => null,
                'password_confirmation' => null,
            ])
            ->assertForbidden();

        $this->assertSame('active', $admin->refresh()->status);
        $this->assertSame('admin', $admin->role);
    }

    public function test_admin_cannot_deactivate_own_account_through_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
                'status' => 'inactive',
                'password' => null,
                'password_confirmation' => null,
            ])
            ->assertForbidden();

        $this->assertTrue($admin->refresh()->isActive());
    }

    public function test_sales_user_cannot_create_users(): void
    {
        $sales = User::factory()->sales()->create();

        $this->actingAs($sales)
            ->post(route('admin.users.store'), [
                'name' => 'Nope',
                'email' => 'nope@example.test',
                'role' => 'sales',
                'status' => 'active',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'nope@example.test',
        ]);
    }

    public function test_stock_user_cannot_update_users(): void
    {
        $stock = User::factory()->stock()->create();
        $target = User::factory()->sales()->create();

        $this->actingAs($stock)
            ->patch(route('admin.users.update', $target), [
                'name' => 'Nope',
                'email' => $target->email,
                'role' => 'sales',
                'status' => 'active',
                'password' => null,
                'password_confirmation' => null,
            ])
            ->assertForbidden();

        $this->assertSame('active', $target->refresh()->status);
    }

    public function test_inactive_admin_is_logged_out_when_creating_users(): void
    {
        $pendingAdmin = User::factory()
            ->admin()
            ->inactive()
            ->create();

        $this->actingAs($pendingAdmin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Ghost',
                'email' => 'ghost@example.test',
                'role' => 'sales',
                'status' => 'active',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', [
            'email' => 'ghost@example.test',
        ]);
    }
}
