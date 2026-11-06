<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('question_section_id')
                ->after('id')
                ->constrained('question_sections')
                ->cascadeOnDelete();

            $table->foreignId('question_type_id')
                ->nullable()
                ->after('question_section_id')
                ->constrained('question_types')
                ->nullOnDelete();

            $table->text('text')->after('question_type_id');
            $table->unsignedInteger('sort_order')->default(0)->after('text');

            $table->index(['question_section_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['question_section_id', 'sort_order']);
            $table->dropConstrainedForeignId('question_type_id');
            $table->dropConstrainedForeignId('question_section_id');
            $table->dropColumn(['text', 'sort_order']);
        });
    }
};
