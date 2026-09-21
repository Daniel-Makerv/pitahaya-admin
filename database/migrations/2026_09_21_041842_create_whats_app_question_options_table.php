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
        Schema::create('whats_app_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')
                ->constrained('whats_app_questions')
                ->cascadeOnDelete();

            $table->string('text');

            $table->integer('score')->default(0);

            $table->unsignedInteger('sort_order')->default(0);

            $table->foreignId('next_block_id')
                ->nullable()
                ->constrained('whats_app_form_blocks')
                ->nullOnDelete();

            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_question_options');
    }
};
