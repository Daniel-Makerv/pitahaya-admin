<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whats_app_form_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')
                ->constrained('whats_app_forms')
                ->cascadeOnDelete();

            $table->string('name');

            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('next_block_id')
                ->nullable()
                ->constrained('whats_app_form_blocks')
                ->nullOnDelete();

            $table->boolean('is_start')->default(false);
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_form_blocks');
    }
};
