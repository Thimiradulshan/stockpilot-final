<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('invoice_number', 50)->unique();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->date('invoice_date');

            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);

            $table->string('status', 20)->default('completed');
            $table->string('payment_status', 30)->default('unpaid');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index(['customer_id', 'invoice_date']);
            $table->index(['status', 'invoice_date']);
            $table->index(['payment_status', 'invoice_date']);
        });

        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price', 15, 2);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('line_total', 15, 2);

            $table->timestamps();

            $table->index(['invoice_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->dateTime('payment_date');

            $table->decimal('amount', 15, 2);

            $table->string('payment_method', 30);

            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('received_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index(['invoice_id', 'payment_date']);
            $table->index(['payment_method', 'payment_date']);
        });
    }


public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
