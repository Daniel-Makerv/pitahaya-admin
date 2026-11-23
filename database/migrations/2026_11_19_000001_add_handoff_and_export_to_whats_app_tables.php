<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Chatbot de WhatsApp:
 * - ai_paused_until: hasta cuándo se calla el agente porque contestó una persona desde la app del celular
 * - exported_form_id: registro de forms que se creó al terminar el cuestionario
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whats_app_conversations', function (Blueprint $table) {
            $table->timestamp('ai_paused_until')->nullable()->after('mode');
        });

        Schema::table('whats_app_form_sessions', function (Blueprint $table) {
            $table->foreignId('exported_form_id')
                ->nullable()
                ->after('completed_at')
                ->constrained('forms')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('whats_app_form_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exported_form_id');
        });

        Schema::table('whats_app_conversations', function (Blueprint $table) {
            $table->dropColumn('ai_paused_until');
        });
    }
};
