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
        Schema::create('whats_app_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')
                ->constrained('whats_app_form_blocks')
                ->cascadeOnDelete();

            $table->text('text');

            $table->enum('type', [
                'text',
                'number',
                'single_choice',
                'multiple_choice',
            ])->default('text');

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('required')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_questions');
    }
};
