<?php

namespace Tests\Feature\Admin;

use App\Enums\StockMovementType;
use App\Livewire\Admin\Ledger\Index as LedgerIndex;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.ledger.index'))
            ->assertRedirect(route('login'));
    }

    public function test_inactive_user_is_redirected_to_login(): void
    {
        $user = User::factory()->admin()->inactive()->create();

        $this->assertGuest();

        $this->get(route('admin.ledger.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_open_the_stock_ledger(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(LedgerIndex::class)
            ->assertOk();
    }

    public function test_stock_user_can_open_the_stock_ledger(): void
    {
        $stock = User::factory()->stock()->create();

        Livewire::actingAs($stock)
            ->test(LedgerIndex::class)
            ->assertOk();
    }

    public function test_sales_user_cannot_open_the_stock_ledger(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(LedgerIndex::class)
            ->assertForbidden();
    }

    public function test_ledger_lists_movements_with_product_and_operator_columns(): void
    {
        $admin = User::factory()->admin()->create();

        $product = Product::factory()->create([
            'sku' => 'LEDGER-SKU-1',
        ]);

        StockMovement::factory()->create([
            'product_id' => $product->getKey(),
            'movement_type' => StockMovementType::PURCHASE,
            'quantity' => 10,
            'quantity_before' => 0,
            'quantity_after' => 10,
            'created_by' => $admin->getKey(),
        ]);

        Livewire::actingAs($admin)
            ->test(LedgerIndex::class)
            ->assertOk()
            ->assertSee('LEDGER-SKU-1')
            ->assertSee('SKU')
            ->assertSee('Operator');
    }
}
