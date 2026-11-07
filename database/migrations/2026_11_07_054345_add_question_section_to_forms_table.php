<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los registros de la PWA ahora pertenecen a una pregunta principal (question_sections).
 * - type_form_id pasa a ser opcional (solo lo usan los grupos viejos de respaldo)
 * - uuid = id del registro generado en la PWA; único para no guardar duplicados
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->unsignedBigInteger('type_form_id')->nullable()->change();

            $table->foreignId('question_section_id')
                ->nullable()
                ->after('type_form_id')
                ->constrained('question_sections')
                ->nullOnDelete();

            $table->unique('uuid');
        });
    }

    public function down(): void
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropUnique(['uuid']);
            $table->dropConstrainedForeignId('question_section_id');
        });
    }
};
