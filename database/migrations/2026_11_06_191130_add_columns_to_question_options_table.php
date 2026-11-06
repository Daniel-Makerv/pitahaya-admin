<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->foreignId('question_id')
                ->after('id')
                ->constrained('questions')
                ->cascadeOnDelete();

            $table->string('text')->after('question_id');
            $table->unsignedInteger('sort_order')->default(0)->after('text');

            $table->index(['question_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropIndex(['question_id', 'sort_order']);
            $table->dropConstrainedForeignId('question_id');
            $table->dropColumn(['text', 'sort_order']);
        });
    }
};
