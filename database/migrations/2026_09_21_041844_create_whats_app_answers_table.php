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
        Schema::create('whats_app_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_session_id')
                ->constrained('whats_app_form_sessions')
                ->cascadeOnDelete();

            $table->foreignId('question_id')
                ->constrained('whats_app_questions')
                ->cascadeOnDelete();

            $table->foreignId('option_id')
                ->nullable()
                ->constrained('whats_app_question_options')
                ->nullOnDelete();

            $table->text('answer')->nullable();

            $table->integer('score')->default(0);

            $table->unique([
                'form_session_id',
                'question_id',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_answers');
    }
};
