<?php

namespace Database\Seeders;

use App\Models\QuestionSection;
use Illuminate\Database\Seeder;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        QuestionSection::create([
            'title' => 'Planta',
        ]);

        QuestionSection::create([
            'title' => 'Alianzas productivas',
        ]);

        QuestionSection::create([
            'title' => 'Canales comerciales',
        ]);

        QuestionSection::create([
            'title' => 'Asesoría técnica',
        ]);

        QuestionSection::create([
            'title' => 'Proceso/Congelado/Deshidratado',
        ]);

        QuestionSection::create([
            'title' => 'Colaboraciones',
        ]);

        QuestionSection::create([
            'title' => 'Solo Información',
        ]);
    }
}
