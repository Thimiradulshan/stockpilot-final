<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('scope', 30);
            $table->date('sequence_date');
            $table->unsignedBigInteger('last_number')->default(0);

            $table->timestamps();

            $table->unique(['scope', 'sequence_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
