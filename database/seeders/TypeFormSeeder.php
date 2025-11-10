<?php

namespace Database\Seeders;

use App\Models\TypeForm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "name" => "Planta / Esqueje",
                "str"  => "plantOrEsqueaje",
            ],
            [
                "name" => "Fruta (Nacional / Exportación)",
                "str"  => "frut",
            ],
            [
                "name" => "Asesoría Técnica",
                "str"  => "asesoryTec",
            ],
            [
                "name" => "Procesado (Congelado / Deshidratado)",
                "str"  => "process",
            ],
            [
                "name" => "Proveedores / Alianzas / Estancias",
                "str"  => "providers",
            ],
            [
                "name" => "Solo Información",
                "str"  => "info",
            ],
        ];

        foreach ($data as $item) {
            TypeForm::create([
                'name' => $item['name'],
                'str'  => $item['str'],
            ]);
        }

    }
}
