<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('movement_type', 30);


            $table->decimal('quantity', 15, 3);

            $table->decimal('quantity_before', 15, 3);
            $table->decimal('quantity_after', 15, 3);

            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('reason', 255)->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['movement_type', 'created_at']);
        });
    }


public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
