<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raffles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('prize')->nullable();                      // un premio por línea (1er, 2º, 3er lugar...)
            $table->unsignedTinyInteger('digits')->default(5);      // cifras del número (5 = igual que el premio mayor)
            $table->date('draw_date')->nullable();                  // fecha del sorteo de la Lotería Nacional
            $table->enum('status', ['open', 'drawn'])->default('open');

            // Resultado del sorteo
            $table->string('lottery_draw')->nullable();             // número de sorteo de la Lotería (ej. 3912)
            $table->string('lottery_number')->nullable();           // premio mayor capturado (ej. 45821)
            $table->unsignedInteger('target_number')->nullable();   // últimas cifras usadas (ej. 45821)
            $table->timestamp('drawn_at')->nullable();

            $table->timestamps();
        });

        Schema::create('raffle_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raffle_id')->constrained('raffles')->cascadeOnDelete();
            $table->foreignId('form_id')->nullable()->constrained('forms')->nullOnDelete();

            $table->string('name');
            $table->string('email');
            $table->string('token', 64)->unique();                  // enlace del correo de invitación

            $table->enum('status', ['invited', 'confirmed'])->default('invited');
            $table->unsignedInteger('number')->nullable();          // número de rifa, se asigna al confirmar
            $table->json('data')->nullable();                       // respuestas del registro de productor
            $table->unsignedTinyInteger('step')->default(0);        // último paso guardado del registro (0-6)
            $table->unsignedTinyInteger('score')->nullable();       // compatibilidad 0-100 (ScoringPitamex)
            $table->char('priority', 1)->nullable();                // A | B | C
            $table->unsignedTinyInteger('place')->nullable();       // lugar ganado en el sorteo (1, 2, 3...)

            $table->timestamp('invited_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['raffle_id', 'email']);                 // una invitación por correo en cada rifa
            $table->unique(['raffle_id', 'number']);                // un número por persona en cada rifa
            $table->index(['raffle_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raffle_participants');
        Schema::dropIfExists('raffles');
    }
};
