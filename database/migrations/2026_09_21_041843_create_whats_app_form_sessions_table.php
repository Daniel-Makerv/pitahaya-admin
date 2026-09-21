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
        Schema::create('whats_app_form_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_conversation_id')
                ->constrained('whats_app_conversations')
                ->cascadeOnDelete();

            $table->foreignId('form_id')
                ->constrained('whats_app_forms')
                ->cascadeOnDelete();

            $table->foreignId('current_block_id')
                ->nullable()
                ->constrained('whats_app_form_blocks')
                ->nullOnDelete();

            $table->foreignId('current_question_id')
                ->nullable()
                ->constrained('whats_app_questions')
                ->nullOnDelete();

            $table->enum('status', [
                'in_progress',
                'completed',
                'cancelled',
            ])->default('in_progress');

            $table->integer('score')->default(0);

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_form_sessions');
    }
};
