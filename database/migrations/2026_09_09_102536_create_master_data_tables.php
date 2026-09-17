<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index('status');
            $table->unique('name');
        });

        Schema::create('suppliers', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('name', 150);
            $table->string('company', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index('status');
            $table->index('company');
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 200);
            $table->string('sku', 100)->unique();

            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('reorder_level', 15, 3)->default(0);

            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index(['category_id', 'status']);
            $table->index('name');
        });

        Schema::create('product_supplier', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->string('supplier_product_code', 100)->nullable();
            $table->decimal('last_cost', 15, 2)->nullable();
            $table->boolean('is_preferred')->default(false);

            $table->timestamps();

            $table->primary(['product_id', 'supplier_id']);
            $table->index(['supplier_id', 'is_preferred']);
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('name', 150);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 20)->default('active');

            $table->timestamps();

            $table->index('status');
            $table->index('name');
        });
    }


public function down(): void
    {
        Schema::dropIfExists('product_supplier');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
    }
};
