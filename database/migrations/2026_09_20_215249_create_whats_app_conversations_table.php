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
        Schema::create('whats_app_conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('whatsapp_contact_id')
                ->constrained('whats_app_contacts')
                ->cascadeOnDelete();

            $table->enum('mode', [
                'ai',
                'human',
                'paused',
            ])->default('ai');

            $table->enum('status', [
                'open',
                'closed',
            ])->default('open');

            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whats_app_conversations');
    }
};
