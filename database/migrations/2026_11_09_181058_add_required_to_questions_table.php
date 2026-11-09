<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preguntas obligatorias u opcionales.
 * Las preguntas que ya existen quedan como obligatorias (igual que hoy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->boolean('required')->default(true)->after('text');
        });

        Schema::table('questions_base', function (Blueprint $table) {
            $table->boolean('required')->default(true)->after('text');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('required');
        });

        Schema::table('questions_base', function (Blueprint $table) {
            $table->dropColumn('required');
        });
    }
};
