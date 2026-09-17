<?php

namespace Database\Seeders;

use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{

private array $products = [
        [
            'sku' => 'GN-1001',
            'name' => 'A4 Printer Paper (Ream)',
            'cost_price' => 3200.00,
            'selling_price' => 3800.00,
            'quantity' => 40,
            'reorder_level' => 10,
            'category' => 'General',
        ],
        [
            'sku' => 'GN-1002',
            'name' => 'Ballpoint Pen (Box)',
            'cost_price' => 550.00,
            'selling_price' => 750.00,
            'quantity' => 120,
            'reorder_level' => 25,
            'category' => 'Consumables',
        ],
        [
            'sku' => 'GN-1003',
            'name' => 'Sticky Notes Pad',
            'cost_price' => 220.00,
            'selling_price' => 320.00,
            'quantity' => 60,
            'reorder_level' => 15,
            'category' => 'General',
        ],
        [
            'sku' => 'GN-1004',
            'name' => 'Ring Binder A4',
            'cost_price' => 420.00,
            'selling_price' => 580.00,
            'quantity' => 0,
            'reorder_level' => 10,
            'category' => 'Accessories',
        ],
        [
            'sku' => 'GN-1005',
            'name' => 'Desk Stapler',
            'cost_price' => 900.00,
            'selling_price' => 1250.00,
            'quantity' => 15,
            'reorder_level' => 20,
            'category' => 'Accessories',
        ],
        [
            'sku' => 'GN-1006',
            'name' => 'Whiteboard Markers (Box)',
            'cost_price' => 1300.00,
            'selling_price' => 1650.00,
            'quantity' => 8,
            'reorder_level' => 6,
            'category' => 'Consumables',
        ],
        [
            'sku' => 'GN-1007',
            'name' => 'Ruler 30cm',
            'cost_price' => 120.00,
            'selling_price' => 200.00,
            'quantity' => 80,
            'reorder_level' => 20,
            'category' => 'General',
        ],
        [
            'sku' => 'GN-1008',
            'name' => 'Scissors 8 Inch',
            'cost_price' => 600.00,
            'selling_price' => 850.00,
            'quantity' => 25,
            'reorder_level' => 10,
            'category' => 'Accessories',
        ],
    ];


public function run(): void
    {
        $admin = User::query()
            ->where('role', UserRole::ADMIN)
            ->first();

        if ($admin === null) {
            $this->command->warn(
                'No administrator found. Run AdminUserSeeder first.'
            );

            return;
        }

        $demoUsers = [
            [
                'name' => 'Sales Demo',
                'email' => 'sales@stockpilot.app',
                'role' => UserRole::SALES,
            ],
            [
                'name' => 'Stock Demo',
                'email' => 'stock@stockpilot.app',
                'role' => UserRole::STOCK,
            ],
        ];

        foreach ($demoUsers as $demoUser) {
            User::query()->firstOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'role' => $demoUser['role'],
                    'status' => 'active',
                ],
            );
        }

        $categories = [
            'General',
            'Consumables',
            'Accessories',
        ];

        foreach ($categories as $categoryName) {
            Category::query()->firstOrCreate(
                ['name' => $categoryName],
                [
                    'description' => 'Demo '.$categoryName.' category.',
                    'status' => 'active',
                ],
            );
        }

        $suppliers = [
            ['name' => 'Office Supplies Direct', 'phone' => '011-2345678', 'status' => 'active'],
            ['name' => 'Stationery World', 'phone' => '011-8765432', 'status' => 'active'],
            ['name' => 'Markers & More', 'phone' => '011-3456789', 'status' => 'active'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::query()->firstOrCreate(
                ['name' => $supplier['name']],
                [
                    'phone' => $supplier['phone'],
                    'status' => $supplier['status'],
                ],
            );
        }

        $customers = [
            ['name' => (string) config('stockpilot.walk_in_customer_name'), 'phone' => '0'],
            ['name' => 'Lahiru Fernando'],
            ['name' => 'Dinithi Jayasuriya'],
            ['name' => 'Ruwan Silva'],
            ['name' => 'Sachini Wijesinghe'],
            ['name' => 'Tharindu Rathnayake'],
            ['name' => 'Nadeesha Perera'],
        ];

        foreach ($customers as $customer) {
            Customer::query()->firstOrCreate(
                ['name' => $customer['name']],
                [
                    'phone' => $customer['phone'] ?? null,
                    'status' => 'active',
                ],
            );
        }

        foreach ($this->products as $productData) {
            $category = Category::query()
                ->where('name', $productData['category'])
                ->firstOrFail();

            $product = Product::query()->firstOrCreate(
                ['sku' => $productData['sku']],
                [
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'cost_price' => $productData['cost_price'],
                    'selling_price' => $productData['selling_price'],
                    'quantity' => $productData['quantity'],
                    'reorder_level' => $productData['reorder_level'],
                    'status' => 'active',
                ],
            );

            if ($product->wasRecentlyCreated) {
                StockMovement::query()->create([
                    'product_id' => $product->id,
                    'movement_type' => StockMovementType::ADJUSTMENT,
                    'quantity' => $productData['quantity'],
                    'quantity_before' => 0,
                    'quantity_after' => $productData['quantity'],
                    'reason' => 'Initial demo stock',
                    'created_by' => $admin->id,
                ]);
            }
        }

        $this->command->info('Demo data seeded successfully.');
    }
}
