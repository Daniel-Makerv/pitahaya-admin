<?php

namespace Database\Seeders;

use App\Models\TypeForm;
use Illuminate\Database\Seeder;

class TypeFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // [
            //     "name" => "Planta / Esqueje",
            //     "str"  => "plantOrEsqueaje",
            //     "headers" => [
            //         'Fecha (DD/MM/AAAA)',
            //         'Nombre',
            //         'Teléfono / WhatsApp',
            //         'Municipio / Estado',
            //         'Tipo de terreno',
            //         'Superficie (ha)',
            //         'Disponibilidad de agua',
            //         '¿Zona con heladas?',
            //         '¿Tiene energía eléctrica?',
            //         '¿Cuenta con mano de obra?',
            //         'Nivel de decisión',
            //         '¿en qué cultivos tienes experiencia?',
            //         'Tiempo estimado para iniciar',
            //         'Notas',
            //     ],
            // ],
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
                "headers" => [
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
                "headers" => [
                    'Fecha',
                    'Nombre',
                    'Teléfono',
                    'Ciudad/Estado',
                    'Rol',
                    'Uso',
                    'Planta',
                    'Proceso',
                    'Volumen',
                    'Frecuencia',
                    'Estado fruta',
                    'Variedad',
                    'Grado industria',
                    'Lugar entrega',
                    'Flete',
                    'Pago',
                    'Crédito',
                    'Inicio',
                    'Notas',
                ]

            ],
            [
                "name" => "Proveedores / Alianzas / Estancias",
                "str"  => "providers",
                "headers" => [
                    'Fecha',
                    'Nombre',
                    'Teléfono',
                    'Ciudad/Estado',
                    'Perfil',
                    'Interés',
                    'Contacto',
                    'Notas',
                ]

            ],
            // [
            //     "name" => "Solo Información",
            //     "str"  => "info",
            //     "headers" => [
            //         'Fecha',
            //         'Nombre',
            //         'Notas',
            //     ]

            // ],

            // v2
            [
                "name" => "Plant Or Esqueaje",
                "str"  => "plantOrEsqueaje",
                "headers" => [
                    "Nombre completo",
                    "Empresa/Rancho",
                    "Cargo/Puesto",
                    "Área principal",
                    "2.-Teléfono / WhatsApp",
                    "2.-Correo",
                    "2.-País",
                    "3.- Estado/Ciudad",
                    "4.-Tipo de terreno",
                    "Cargo/Puesto",
                    "Superficie disponible",
                    "Tipo de clima",
                    "Temperatura mínima aproximada",
                    "¿Se presentan heladas?",
                    "Disponibilidad de agua",
                    "¿Cuenta con sistema de riego?",
                    "Rango de inversión disponible",
                    "¿Le interesaría comercializar con PITAMEX?",
                ]
            ],
            [
                "name" => "Productive Alliances",
                "str"  => "productive_alliances",
                "headers" => [
                    "Nombre completo",
                    "Empresa/Rancho",
                    "Cargo/Puesto",
                    "Área principal",
                    "2.-Teléfono / WhatsApp",
                    "2.-Correo",
                    "2.-País",
                    "3.- Estado/Ciudad",
                    "Producción anual",
                    "Semanas de producción",
                    "Variedades principales",
                    "Tipo de clima",
                    "Grados Brix",
                    "¿Cuenta con certificaciones?",
                    "¿Le interesa producción limpia/libre de pesticidas?",
                    "¿Cuenta con cadena de frío?",
                    "¿Busca relación de largo plazo?",
                ]
            ],
            [
                "name" => "Commercial Channels",
                "str"  => "commercial_channels",
                "headers" => [
                    "Nombre completo",
                    "Empresa/Rancho",
                    "Cargo/Puesto",
                    "Área principal",
                    "2.-Teléfono / WhatsApp",
                    "2.-Correo",
                    "2.-País",
                    "3.- Estado/Ciudad",
                    "Área principal",
                    "Canal principal",
                    "Volumen requerido",
                    "Variedades de interés",
                    "Presentación deseada",
                    "¿Qué certificaciones requiere?",
                    "¿Qué documentos solicita?",
                    "Método de pago",
                    "Tiempo de pago",
                    "¿Busca proveedor permanente?",
                ]
            ],
            [
                "name" => "Technical Advice",
                "str"  => "technical_advice",
                "headers" => [
                    "Nombre completo",
                    "Empresa/Rancho",
                    "Cargo/Puesto",
                    "Área principal",
                    "2.-Teléfono / WhatsApp",
                    "2.-Correo",
                    "2.-País",
                    "3.- Estado/Ciudad",
                    "¿Qué necesita?",
                    "¿Cuenta con huerta establecida?",
                    "Superficie",
                ]
            ],

            [
                "name" => "Processing Frozen",
                "str"  => "processing_frozen",
                "headers" => [
                    "Nombre completo",
                    "Empresa/Rancho",
                    "Cargo/Puesto",
                    "Área principal",
                    "2.-Teléfono / WhatsApp",
                    "2.-Correo",
                    "2.-País",
                    "3.- Estado/Ciudad",
                    "Cargo/Puesto",
                    "¿Qué le interesa?",
                    "Volumen requerido",
                    "¿Requiere certificaciones?",
                ]
            ],
            [
                "name" => "Collaborations",
                "str"  => "collaborations",
                "headers" => [
                    "Nombre completo",
                    "Empresa/Rancho",
                    "Cargo/Puesto",
                    "Área principal",
                    "2.-Teléfono / WhatsApp",
                    "2.-Correo",
                    "2.-País",
                    "3.- Estado/Ciudad",
                    "Tipo de colaboración",
                    "¿Qué busca aportar?",
                ]
            ],
            [
                "name" => "Info",
                "str"  => "info",
                "headers" => [
                    "Nombre completo",
                    "Cargo/Puesto",
                    "¿Qué información le interesa?",
                ]
            ]
        ];

        foreach ($data as $item) {
            TypeForm::firstOrCreate(
                [
                    'str' => $item['str'],
                ],
                [
                    'name' => $item['name'],
                    'headers' => json_encode($item['headers'], JSON_UNESCAPED_UNICODE),
                ]
            );
        }
    }
}
