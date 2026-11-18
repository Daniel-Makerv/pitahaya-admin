<?php

namespace App\Support;

/**
 * Preguntas del registro de productor PITAMEX (página de la rifa), agrupadas por paso.
 * Sirve para mostrar las respuestas en el admin con el texto de cada pregunta.
 *
 * Si cambias las preguntas en resources/views/raffles/invitation.blade.php (STEPS),
 * actualiza también esta lista.
 */
class RegistroPitamex
{
    public const SECTIONS = [
        [
            'title'     => 'Sus datos',
            'questions' => [
                'nombre'           => 'Nombre completo',
                'telefono'         => 'Teléfono (WhatsApp)',
                'correo'           => 'Correo electrónico',
                'estado'           => 'Estado',
                'municipio'        => 'Municipio',
                'conocio'          => '¿Cómo conoció PITAMEX?',
            ],
        ],
        [
            'title'     => 'Su predio',
            'questions' => [
                'superficie'       => 'Superficie disponible',
                'propia'           => '¿La tierra es propia?',
                'propiedad'        => 'Tipo de propiedad',
                'terreno'          => 'Tipo de terreno',
                'predios'          => 'Si tiene 2 o más predios, describa las características de los adicionales',
            ],
        ],
        [
            'title'     => 'Clima y condiciones',
            'questions' => [
                'clima'            => 'Tipo de clima',
                'tmax'             => 'Temperatura máxima aproximada',
                'tmin'             => 'Temperatura mínima aproximada',
                'heladas'          => '¿Se presentan heladas?',
                'altitud'          => 'Altitud aproximada',
                'precipitacion'    => 'Precipitación aproximada',
                'precip_mm'        => 'Precipitación pluvial anual (mm), si la conoce',
            ],
        ],
        [
            'title'     => 'Agua, infraestructura y suelo',
            'questions' => [
                'fuente'           => 'Fuente principal de agua',
                'disponibilidad'   => 'Disponibilidad de agua',
                'agua_max'         => 'Cantidad máxima de agua disponible hoy en el predio',
                'riego'            => '¿Cuenta con riego?',
                'tipo_riego'       => 'Tipo de riego',
                'electricidad'     => '¿Cuenta con electricidad?',
                'conagua'          => '¿Cuenta con documentación vigente de CONAGUA?',
                'conoce_suelo'     => '¿Conoce el tipo de suelo?',
                'tipo_suelo'       => 'Tipo de suelo',
                'analisis'         => '¿Cuenta con análisis de suelo?',
                'ph'               => 'pH aproximado',
                'drenaje'          => '¿Problemas de drenaje?',
            ],
        ],
        [
            'title'     => 'Experiencia y capacidad',
            'questions' => [
                'experiencia'      => 'Experiencia agrícola',
                'cultivos'         => 'Cultivos actuales',
                'asesor'           => '¿Cuenta con asesor técnico?',
                'personal'         => 'Personal agrícola fijo',
                'certificacion'    => '¿Cuenta con alguna certificación (Primus, Global G.A.P.)?',
                'inversion'        => 'Rango de inversión disponible',
                'financiamiento'   => '¿El proyecto dependería de financiamiento?',
                'infraestructura'  => 'Infraestructura disponible',
                'factura'          => '¿Factura actividad agrícola?',
                'regimen'          => 'Régimen fiscal',
                'acta'             => '¿Cuenta con acta constitutiva?',
                'regularizada'     => '¿La propiedad está regularizada?',
            ],
        ],
        [
            'title'     => 'Su visión y su proyecto',
            'questions' => [
                'busca'            => '¿Qué busca principalmente?',
                'comercializar'    => '¿Le interesaría comercializar con PITAMEX?',
                'limpia'           => '¿Le interesa la producción limpia o libre de pesticidas?',
                'inocuidad'        => '¿Le interesaría producir con inocuidad?',
                'valora'           => '¿Qué valora más?',
                'etapa'            => '¿En qué etapa está su proyecto?',
                'decision'         => 'Nivel de decisión',
                'iniciar'          => 'Tiempo estimado para iniciar',
                'prioridad'        => '¿Cuál sería su prioridad al desarrollar un proyecto de pitahaya?',
                'comentarios'      => 'Comentarios, dudas o preguntas',
            ],
        ],
    ];

    /**
     * Respuestas de un participante en el orden del registro:
     * [['title' => 'Sus datos', 'answers' => [['question' => ..., 'answer' => ...]]], ...]
     * Las respuestas con llaves que ya no existen en el registro van al final, en "Otras respuestas".
     */
    public static function answers(array $data): array
    {
        $known = [];
        $sections = [];

        foreach (self::SECTIONS as $section) {
            $answers = [];

            foreach ($section['questions'] as $key => $question) {
                $known[] = $key;

                if (! array_key_exists($key, $data) || self::isEmpty($data[$key])) {
                    continue;
                }

                $answers[] = ['question' => $question, 'answer' => self::format($data[$key])];
            }

            $sections[] = [
                'title'    => $section['title'],
                'answers'  => $answers,
                'total'    => count($section['questions']),
            ];
        }

        $others = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $known, true) || str_starts_with((string) $key, '_') || $key === 'ubicacion' || self::isEmpty($value)) {
                continue;
            }
            $others[] = ['question' => ucfirst(str_replace('_', ' ', (string) $key)), 'answer' => self::format($value)];
        }

        if ($others) {
            $sections[] = ['title' => 'Otras respuestas', 'answers' => $others, 'total' => count($others)];
        }

        return $sections;
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private static function format(mixed $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map('strval', array_filter($value, 'is_scalar')));
        }

        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }

        return trim((string) $value);
    }
}
