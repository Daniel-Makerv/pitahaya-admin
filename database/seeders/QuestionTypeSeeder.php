<?php

namespace Database\Seeders;

use App\Models\QuestionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        QuestionType::insert([
            ['name' => 'Texto'],
            ['name' => 'Número'],
            ['name' => 'Sí/No'],
            ['name' => 'Selección única'],
            ['name' => 'Selección múltiple'],
            ['name' => 'Escala'],
        ]);
    }
}
