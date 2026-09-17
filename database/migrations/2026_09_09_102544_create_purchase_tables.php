<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('purchase_number', 50)->unique();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->date('purchase_date');

            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            $table->string('status', 20)->default('completed');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index(['supplier_id', 'purchase_date']);
            $table->index(['status', 'purchase_date']);
        });

        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);

            $table->timestamps();

            $table->index(['purchase_id', 'product_id']);
            $table->index('product_id');
        });
    }


public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
