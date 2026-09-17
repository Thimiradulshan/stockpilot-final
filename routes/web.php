<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Customers\Index as CustomersIndex;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Ledger\Index as LedgerIndex;
use App\Livewire\Admin\Pos\Index as PosIndex;
use App\Livewire\Admin\Products\Index as ProductsIndex;
use App\Livewire\Admin\Purchases\Index as PurchasesIndex;
use App\Livewire\Admin\Reports\Index as ReportsIndex;
use App\Livewire\Admin\Sales\Index as SalesIndex;
use App\Livewire\Admin\Statements\Index as StatementsIndex;
use App\Livewire\Admin\Suppliers\Index as SuppliersIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'))->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');



    Route::get('admin/users', UsersIndex::class)
        ->middleware('role:admin')
        ->name('admin.users.index');

    Route::post('admin/users', [UserController::class, 'store'])
        ->middleware(['role:admin', 'throttle:60,1'])
        ->name('admin.users.store');

    Route::patch('admin/users/{user}', [UserController::class, 'update'])
        ->middleware(['role:admin', 'throttle:60,1'])
        ->name('admin.users.update');



    Route::get('admin/categories', CategoriesIndex::class)
        ->middleware('role:admin,stock')
        ->name('admin.categories.index');

    Route::post('admin/categories', [CategoryController::class, 'store'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.categories.store');

    Route::patch('admin/categories/{category}', [CategoryController::class, 'update'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.categories.update');



    Route::get('admin/suppliers', SuppliersIndex::class)
        ->middleware('role:admin,stock')
        ->name('admin.suppliers.index');

    Route::post('admin/suppliers', [SupplierController::class, 'store'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.suppliers.store');

    Route::patch('admin/suppliers/{supplier}', [SupplierController::class, 'update'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.suppliers.update');



    Route::get('admin/products', ProductsIndex::class)
        ->middleware('role:admin,stock')
        ->name('admin.products.index');

    Route::post('admin/products', [ProductController::class, 'store'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.products.store');

    Route::patch('admin/products/{product}', [ProductController::class, 'update'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.products.update');

    Route::patch('admin/products/{product}/stock', [
        ProductController::class,
        'adjustStock',
    ])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.products.stock.adjust');



    Route::get('admin/customers', CustomersIndex::class)
        ->middleware('role:admin,sales')
        ->name('admin.customers.index');

    Route::post('admin/customers', [CustomerController::class, 'store'])
        ->middleware(['role:admin,sales', 'throttle:60,1'])
        ->name('admin.customers.store');

    Route::patch('admin/customers/{customer}', [CustomerController::class, 'update'])
        ->middleware(['role:admin,sales', 'throttle:60,1'])
        ->name('admin.customers.update');



    Route::get('admin/purchases', PurchasesIndex::class)
        ->middleware('role:admin,stock')
        ->name('admin.purchases.index');

    Route::post('admin/purchases', [PurchaseController::class, 'store'])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.purchases.store');

    Route::post('admin/purchases/{purchase}/cancel', [
        PurchaseController::class,
        'cancel',
    ])
        ->middleware(['role:admin,stock', 'throttle:60,1'])
        ->name('admin.purchases.cancel');



    Route::get('admin/sales', SalesIndex::class)
        ->middleware('role:admin,sales')
        ->name('admin.sales.index');

    Route::post('admin/sales', [InvoiceController::class, 'store'])
        ->middleware(['role:admin,sales', 'throttle:60,1'])
        ->name('admin.sales.store');

    Route::post('admin/sales/{invoice}/void', [
        InvoiceController::class,
        'void',
    ])
        ->middleware(['role:admin,sales', 'throttle:60,1'])
        ->name('admin.sales.void');

    Route::get('admin/sales/{invoice}/print', [
        InvoiceController::class,
        'print',
    ])
        ->middleware('role:admin,sales')
        ->name('admin.sales.print');

    Route::post('admin/sales/{invoice}/payments', [
        PaymentController::class,
        'store',
    ])
        ->middleware(['role:admin,sales', 'throttle:60,1'])
        ->name('admin.sales.payments.store');



    Route::get('admin/pos', PosIndex::class)
        ->middleware('role:admin,sales')
        ->name('admin.pos.index');



    Route::get('admin/reports', ReportsIndex::class)
        ->middleware('role:admin,sales,stock')
        ->name('admin.reports.index');



    Route::get('admin/statements', StatementsIndex::class)
        ->middleware('role:admin,sales')
        ->name('admin.statements.index');

    Route::get('admin/ledger', LedgerIndex::class)
        ->middleware('role:admin,stock')
        ->name('admin.ledger.index');
});

require __DIR__.'/settings.php';
