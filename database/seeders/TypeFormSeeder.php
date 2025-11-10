<?php

namespace Database\Seeders;

use App\Models\TypeForm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class
TypeFormSeeder extends Seeder
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
"headers"  => [
    'Fecha (DD/MM/AAAA)',
    'Nombre',
    'Teléfono / WhatsApp',
    'Municipio / Estado',
    'Tipo de terreno',
    'Superficie (ha)',
    'Disponibilidad de agua',
    '¿Zona con heladas?',
    '¿Tiene energía eléctrica?',
    '¿Cuenta con mano de obra?',
    'Nivel de decisión',
    'Tiempo estimado para iniciar',
    'Notas',
],


            ],
            [
                "name" => "Fruta (Nacional / Exportación)",
                "str"  => "frut",
               "headers" => [
                    'Fecha (DD/MM/AAAA)',
                    'Nombre',
                    'Teléfono / WhatsApp',
                    'Ciudad / Estado',
                    'Tipo de fruta',
                    'Volumen requerido (kg)',
                    'Frecuencia de compra',
                    'Presentación',
                    'Calidad',
                    'Lugar de entrega',
                    '¿Quién cubre flete?',
                    '¿Tiene transporte?',
                    'Modalidad de pago?',
                    'Días de crédito',
                    'Tiempo para iniciar',
                    'Notas',
                ],

            ],
            [
                "name" => "Asesoría Técnica",
                "str"  => "asesoryTec",
                "headers"  => [
                    'Fecha (DD/MM/AAAA)',
                    'Nombre',
                    'Teléfono / WhatsApp',
                    'Municipio / Estado',
                    'Años con cultivo',
                    'Superficie (ha)',
                    'Cantidad de plantas',
                    'Variedades',
                    'Propósito del cultivo',
                    'Tipo de tutor',
                    'Sistema de riego',
                    'Manejo de fertilización',
                    'Problemas de plagas/enfermedades',
                    'Estado del cultivo',
                    'Producción actual',
                    'Objetivo principal',
                    'Tipo de asesoría buscada',
                    'Nivel de involucramiento',
                    'Compromiso para implementar',
                    '¿Desea visita técnica?',
                    'Notas',
                ],
            ],
            [
                "name" => "Procesado (Congelado / Deshidratado)",
                "str"  => "process",
                "headers"  => "",
            ],
            [
                "name" => "Proveedores / Alianzas / Estancias",
                "str"  => "providers",
                "headers"  => "",
            ],
            [
                "name" => "Solo Información",
                "str"  => "info",
                "headers"  => "",
            ],
        ];

        foreach ($data as $item) {
            TypeForm::create([
                'name' => $item['name'],
                'str'  => $item['str'],
                'headers'=> $item['headers'],
            ]);
        }

    }
}
