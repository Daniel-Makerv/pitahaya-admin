<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_types', function (Blueprint $table) {
            $table->string('str', 50)->nullable()->after('name');
        });

        // Tipo de campo que usa cada tipo de pregunta
        $map = [
            'Texto'              => 'text',
            'Número'             => 'number',
            'Sí/No'              => 'checkbox',
            'Selección única'    => 'select',
            'Selección múltiple' => 'select',
            'Escala'             => 'scale',
        ];

        foreach ($map as $name => $str) {
            DB::table('question_types')->where('name', $name)->update(['str' => $str]);
        }
    }

    public function down(): void
    {
        Schema::table('question_types', function (Blueprint $table) {
            $table->dropColumn('str');
        });
    }
};
