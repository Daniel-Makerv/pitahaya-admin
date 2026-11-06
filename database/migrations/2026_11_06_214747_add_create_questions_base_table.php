<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Banco de preguntas reutilizables:
 * - questions_base            => preguntas dadas de alta desde la pantalla principal
 * - questions_base_options    => sus opciones (Selección única / múltiple)
 * - questions.question_base_id => de qué pregunta base se copió cada pregunta secundaria
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions_base', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_type_id')
                ->nullable()
                ->constrained('question_types')
                ->nullOnDelete();
            $table->text('text');
            $table->timestamps();
        });

        Schema::create('questions_base_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_base_id')
                ->constrained('questions_base')
                ->cascadeOnDelete();
            $table->string('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['question_base_id', 'sort_order']);
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('question_base_id')
                ->nullable()
                ->after('question_type_id')
                ->constrained('questions_base')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('question_base_id');
        });

        Schema::dropIfExists('questions_base_options');
        Schema::dropIfExists('questions_base');
    }
};
