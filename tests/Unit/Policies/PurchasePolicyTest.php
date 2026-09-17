<?php

namespace Tests\Unit\Policies;

use App\Models\Purchase;
use App\Models\User;
use App\Policies\PurchasePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_and_create_purchases(): void
    {
        $policy = new PurchasePolicy;
        $user = User::factory()->admin()->create();
        $purchase = new Purchase;

        $this->assertTrue(
            $policy->viewAny($user)
        );

        $this->assertTrue(
            $policy->view($user, $purchase)
        );

        $this->assertTrue(
            $policy->create($user)
        );
    }

    public function test_admin_can_cancel_completed_purchase(): void
    {
        $policy = new PurchasePolicy;
        $user = User::factory()->admin()->create();

        $purchase = new Purchase([
            'status' => 'completed',
        ]);

        $this->assertTrue(
            $policy->cancel($user, $purchase)
        );
    }

    public function test_admin_cannot_cancel_cancelled_purchase(): void
    {
        $policy = new PurchasePolicy;
        $user = User::factory()->admin()->create();

        $purchase = new Purchase([
            'status' => 'cancelled',
        ]);

        $this->assertFalse(
            $policy->cancel($user, $purchase)
        );
    }

    public function test_stock_user_can_view_create_and_cancel_completed_purchases(): void
    {
        $policy = new PurchasePolicy;
        $user = User::factory()->stock()->create();

        $completedPurchase = new Purchase([
            'status' => 'completed',
        ]);

        $cancelledPurchase = new Purchase([
            'status' => 'cancelled',
        ]);

        $this->assertTrue(
            $policy->viewAny($user)
        );

        $this->assertTrue(
            $policy->view($user, $completedPurchase)
        );

        $this->assertTrue(
            $policy->create($user)
        );

        $this->assertTrue(
            $policy->cancel($user, $completedPurchase)
        );

        $this->assertFalse(
            $policy->cancel($user, $cancelledPurchase)
        );
    }

    public function test_sales_user_cannot_access_purchases(): void
    {
        $policy = new PurchasePolicy;
        $user = User::factory()->sales()->create();

        $completedPurchase = new Purchase([
            'status' => 'completed',
        ]);

        $this->assertFalse(
            $policy->viewAny($user)
        );

        $this->assertFalse(
            $policy->view($user, $completedPurchase)
        );

        $this->assertFalse(
            $policy->create($user)
        );

        $this->assertFalse(
            $policy->cancel($user, $completedPurchase)
        );
    }

    public function test_inactive_stock_user_cannot_access_purchases(): void
    {
        $policy = new PurchasePolicy;

        $user = User::factory()
            ->stock()
            ->inactive()
            ->create();

        $purchase = new Purchase([
            'status' => 'completed',
        ]);

        $this->assertFalse(
            $policy->viewAny($user)
        );

        $this->assertFalse(
            $policy->view($user, $purchase)
        );

        $this->assertFalse(
            $policy->create($user)
        );

        $this->assertFalse(
            $policy->cancel($user, $purchase)
        );
    }

    public function test_purchase_update_and_delete_are_disabled(): void
    {
        $policy = new PurchasePolicy;
        $admin = User::factory()->admin()->create();
        $purchase = new Purchase([
            'status' => 'completed',
        ]);

        $this->assertFalse(
            $policy->update($admin, $purchase)
        );

        $this->assertFalse(
            $policy->delete($admin, $purchase)
        );

        $this->assertFalse(
            $policy->restore($admin, $purchase)
        );

        $this->assertFalse(
            $policy->forceDelete($admin, $purchase)
        );
    }
}
