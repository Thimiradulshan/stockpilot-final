<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Pos\Index as PosIndex;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('admin.pos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_stock_user_cannot_access_pos(): void
    {
        $stock = User::factory()->stock()->create();

        $this->actingAs($stock)
            ->get(route('admin.pos.index'))
            ->assertForbidden();

        Livewire::actingAs($stock)
            ->test(PosIndex::class)
            ->assertForbidden();
    }

    public function test_sales_user_can_access_pos_page(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        $this->actingAs($sales)
            ->get(route('admin.pos.index'))
            ->assertOk()
            ->assertSee('Point of Sale')
            ->assertSee($product->name);
    }

    public function test_admin_can_access_pos_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.pos.index'))
            ->assertOk()
            ->assertSee('Point of Sale');
    }

    public function test_walk_in_customer_is_created_and_selected_by_default(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->assertOk();

        $walkIn = Customer::query()
            ->where('name', config('stockpilot.walk_in_customer_name'))
            ->first();

        $this->assertNotNull($walkIn);
        $this->assertTrue($walkIn->isActive());

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->assertSet('customerId', $walkIn->id);
    }

    public function test_product_is_added_to_cart_at_selling_price(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 250.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->assertSet("cart.{$product->id}.quantity", '1')
            ->assertSet("cart.{$product->id}.unit_price", '250.00');
    }

    public function test_adding_same_product_again_increments_quantity(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 120.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->call('addProduct', $product->id)
            ->assertSet("cart.{$product->id}.quantity", '2');
    }

    public function test_removing_product_clears_the_cart_line(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 120.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->assertSet("cart.{$product->id}.quantity", '1')
            ->call('removeFromCart', $product->id)
            ->assertSet('cart', []);
    }

    public function test_out_of_stock_product_cannot_be_added(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()->outOfStock()->create([
            'status' => 'active',
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->assertSet('notice', "{$product->name} is out of stock.")
            ->assertSet('cart', []);
    }

    public function test_inactive_product_cannot_be_added(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()->inactive()->create([
            'quantity' => 10,
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->assertSet('notice', 'The selected product is no longer available.')
            ->assertSet('cart', []);
    }

    public function test_completing_sale_creates_invoice_payment_and_decreases_stock(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->create(['status' => 'active']);

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        $component = Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->set('customerId', $customer->id)
            ->call('addProduct', $product->id)
            ->set('cart.'.$product->id.'.quantity', '3')
            ->set('taxRate', '5.00')
            ->set('paymentMethod', 'card')
            ->call('tenderedExact');

        $component
            ->call('completeSale')
            ->assertOk()
            ->assertSet('successSale.total', '630.00')
            ->assertSet('successSale.paid', '630.00')
            ->assertSet('successSale.change', '0.00')
            ->assertSet('successSale.payment_method', 'card')
            ->assertSet('successSale.payment_status', 'paid')
            ->assertSet('cart', []);

        $invoice = Invoice::query()
            ->where('customer_id', $customer->id)
            ->where('status', 'completed')
            ->firstOrFail();

        $invoiceNumber = $invoice->invoice_number;

        $this->assertSame('600.00', $invoice->subtotal);
        $this->assertSame('5.00', $invoice->tax_rate);
        $this->assertSame('30.00', $invoice->tax_amount);
        $this->assertSame('630.00', $invoice->total_amount);
        $this->assertSame('paid', $invoice->payment_status);
        $this->assertSame($sales->id, $invoice->created_by);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => '630.00',
            'payment_method' => 'card',
            'received_by' => $sales->id,
        ]);

        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => '3.000',
            'unit_price' => '200.00',
            'line_total' => '600.00',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => -3,
            'reference_type' => 'invoice',
            'reference_id' => $invoice->id,
        ]);

        $this->assertSame(
            '7.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_cash_overpayment_records_balance_and_returns_change(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->set('paymentMethod', 'cash')
            ->set('tendered', '500.00')
            ->call('completeSale')
            ->assertOk()
            ->assertSet('successSale.total', '200.00')
            ->assertSet('successSale.paid', '200.00')
            ->assertSet('successSale.change', '300.00')
            ->assertSet('successSale.payment_status', 'paid');

        $invoice = Invoice::query()->firstOrFail();

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => '200.00',
            'payment_method' => 'cash',
        ]);
    }

    public function test_partial_tender_records_a_partial_payment(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 200.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->set('paymentMethod', 'credit')
            ->set('tendered', '80.00')
            ->call('completeSale')
            ->assertOk()
            ->assertSet('successSale.total', '200.00')
            ->assertSet('successSale.paid', '80.00')
            ->assertSet('successSale.change', '0.00')
            ->assertSet('successSale.payment_status', 'partially_paid');

        $invoice = Invoice::query()->firstOrFail();

        $this->assertSame('partially_paid', $invoice->payment_status);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => '80.00',
            'payment_method' => 'credit',
        ]);
    }

    public function test_quantity_exceeding_stock_is_rejected(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(3)
            ->create([
                'selling_price' => 100.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->set("cart.{$product->id}.quantity", '5')
            ->set('tendered', '500.00')
            ->call('completeSale')
            ->assertHasErrors('sale');

        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('payments', 0);

        $this->assertSame(
            '3.000',
            (string) Product::query()
                ->findOrFail($product->id)
                ->quantity
        );
    }

    public function test_empty_cart_cannot_be_completed(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->set('tendered', '100.00')
            ->call('completeSale')
            ->assertHasErrors('cart');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_quick_customer_is_created_and_selected(): void
    {
        $sales = User::factory()->sales()->create();

        $component = Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('showQuickCustomer')
            ->set('quickName', 'Ann Perera')
            ->set('quickPhone', '071-0000000')
            ->call('saveQuickCustomer')
            ->assertOk();

        $customer = Customer::query()->findOrFail(
            $component->get('customerId')
        );

        $this->assertSame('Ann Perera', $customer->name);
        $this->assertSame('071-0000000', $customer->phone);
        $this->assertTrue($customer->isActive());
    }

    public function test_quick_customer_requires_a_name(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('showQuickCustomer')
            ->call('saveQuickCustomer')
            ->assertHasErrors('quickName');

        $this->assertDatabaseCount('customers', 1);
    }

    public function test_inactive_customer_cannot_be_used_for_sale(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->inactive()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 100.00,
                'status' => 'active',
            ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->set('customerId', $customer->id)
            ->call('addProduct', $product->id)
            ->set('tendered', '100.00')
            ->call('completeSale')
            ->assertHasErrors('customerId');

        $this->assertDatabaseCount('invoices', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_reset_sale_clears_the_workspace(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()
            ->withQuantity(10)
            ->create([
                'selling_price' => 100.00,
                'status' => 'active',
            ]);

        $component = Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->set('tendered', '500.00');

        $component
            ->call('completeSale')
            ->assertSet('successSale.invoice_number', $component->get('successSale.invoice_number'))
            ->call('resetSale')
            ->assertSet('cart', [])
            ->assertSet('successSale', null)
            ->assertSet('discountType', 'fixed')
            ->assertSet('discountAmount', '0.00')
            ->assertSet('taxRate', '0.00')
            ->assertSet('paymentMethod', 'cash')
            ->assertSet('tendered', '0.00')
            ->assertOk();
    }

    public function test_customer_search_lists_active_matches_only(): void
    {
        $sales = User::factory()->sales()->create();

        Customer::factory()->create([
            'name' => 'Nimal Perera',
            'status' => 'active',
        ]);

        Customer::factory()->inactive()->create([
            'name' => 'Nimal Inactive',
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->set('customerSearch', 'Nimal')
            ->assertSee('Nimal Perera')
            ->assertDontSee('Nimal Inactive');
    }

    public function test_selecting_a_customer_updates_the_sale_and_clears_search(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->create(['status' => 'active']);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->set('customerSearch', $customer->name)
            ->call('selectCustomer', $customer->id)
            ->assertSet('customerId', $customer->id)
            ->assertSet('customerSearch', '')
            ->assertSet('notice', '');
    }

    public function test_inactive_customer_cannot_be_selected(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->inactive()->create();

        $component = Livewire::actingAs($sales)->test(PosIndex::class);

        $walkInId = $component->get('customerId');

        $component
            ->call('selectCustomer', $customer->id)
            ->assertSet('customerId', $walkInId)
            ->assertSet('notice', 'That customer is not available.');
    }

    public function test_unknown_customer_cannot_be_selected(): void
    {
        $sales = User::factory()->sales()->create();

        $component = Livewire::actingAs($sales)->test(PosIndex::class);

        $walkInId = $component->get('customerId');

        $component
            ->call('selectCustomer', 999999)
            ->assertSet('customerId', $walkInId)
            ->assertSet('notice', 'That customer is not available.');
    }

    public function test_walk_in_customer_can_be_restored(): void
    {
        $sales = User::factory()->sales()->create();

        $customer = Customer::factory()->create(['status' => 'active']);

        $component = Livewire::actingAs($sales)->test(PosIndex::class);

        $walkInId = $component->get('customerId');

        $component
            ->call('selectCustomer', $customer->id)
            ->assertSet('customerId', $customer->id)
            ->call('useWalkInCustomer')
            ->assertSet('customerId', $walkInId);
    }

    public function test_category_filter_limits_the_catalog(): void
    {
        $sales = User::factory()->sales()->create();

        $food = Category::factory()->create([
            'name' => 'Food',
            'status' => 'active',
        ]);

        $drinks = Category::factory()->create([
            'name' => 'Drinks',
            'status' => 'active',
        ]);

        Product::factory()->withQuantity(10)->create([
            'name' => 'Rice Pack',
            'category_id' => $food->id,
            'status' => 'active',
        ]);

        Product::factory()->withQuantity(10)->create([
            'name' => 'Cola Bottle',
            'category_id' => $drinks->id,
            'status' => 'active',
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('selectCategory', $food->id)
            ->assertSet('categoryId', $food->id)
            ->assertSee('Rice Pack')
            ->assertDontSee('Cola Bottle');
    }

    public function test_negative_category_selection_falls_back_to_all_products(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('selectCategory', -5)
            ->assertSet('categoryId', 0);
    }

    public function test_stock_levels_are_flagged_in_the_catalog(): void
    {
        $sales = User::factory()->sales()->create();

        Product::factory()->withQuantity(50)->create([
            'name' => 'Healthy Item',
            'reorder_level' => 5,
            'status' => 'active',
        ]);

        Product::factory()->withQuantity(2)->create([
            'name' => 'Low Item',
            'reorder_level' => 5,
            'status' => 'active',
        ]);

        Product::factory()->outOfStock()->create([
            'name' => 'Empty Item',
            'status' => 'active',
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->assertSee('In stock')
            ->assertSee('Low stock')
            ->assertSee('Out of stock');
    }

    public function test_inactive_products_are_excluded_from_the_catalog(): void
    {
        $sales = User::factory()->sales()->create();

        Product::factory()->inactive()->create([
            'name' => 'Retired Item',
            'quantity' => 10,
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->assertDontSee('Retired Item');
    }

    public function test_increment_and_decrement_adjust_the_cart_line(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()->withQuantity(10)->create([
            'selling_price' => 100.00,
            'status' => 'active',
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->call('incrementQuantity', $product->id)
            ->assertSet("cart.{$product->id}.quantity", '2')
            ->call('decrementQuantity', $product->id)
            ->assertSet("cart.{$product->id}.quantity", '1')
            ->call('decrementQuantity', $product->id)
            ->assertSet('cart', []);
    }

    public function test_completing_a_sale_dispatches_a_success_toast(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()->withQuantity(10)->create([
            'selling_price' => 100.00,
            'status' => 'active',
        ]);

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->set('tendered', '100.00')
            ->call('completeSale')
            ->assertDispatched(
                'stockpilot-toast',
                icon: 'success',
                title: 'Sale completed',
            );
    }

    public function test_failed_sale_dispatches_an_error_toast(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('completeSale')
            ->assertHasErrors('cart')
            ->assertDispatched(
                'stockpilot-toast',
                icon: 'error',
                title: 'Sale could not be completed',
            );
    }

    public function test_quick_customer_dispatches_a_toast_and_closes_modal(): void
    {
        $sales = User::factory()->sales()->create();

        Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('showQuickCustomer')
            ->assertSet('showQuickCustomer', true)
            ->set('quickName', 'Ann Perera')
            ->call('saveQuickCustomer')
            ->assertSet('showQuickCustomer', false)
            ->assertDispatched(
                'stockpilot-toast',
                icon: 'success',
                title: 'Customer added',
            );
    }

    public function test_duplicate_submission_does_not_create_a_second_invoice(): void
    {
        $sales = User::factory()->sales()->create();

        $product = Product::factory()->withQuantity(10)->create([
            'selling_price' => 100.00,
            'status' => 'active',
        ]);

        $component = Livewire::actingAs($sales)
            ->test(PosIndex::class)
            ->call('addProduct', $product->id)
            ->set('tendered', '100.00')
            ->call('completeSale')
            ->assertOk();

        $component
            ->call('addProduct', $product->id)
            ->set('tendered', '100.00')
            ->call('completeSale')
            ->assertDispatched('stockpilot-toast', icon: 'error');

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('payments', 1);
    }
}
