<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)
                ->default('sales')
                ->after('email');

            $table->string('status', 20)
                ->default('active')
                ->after('role');

            $table->index(['role', 'status']);
        });
    }


public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn(['role', 'status']);
        });
    }
};
