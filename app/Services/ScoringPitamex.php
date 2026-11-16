<?php

namespace App\Services;

/**
 * Calificación de compatibilidad · PITAMEX Expo 2026
 *
 * Lee storage/app/scoring-pitamex.json (el mismo 05-scoring-pitamex.json del paquete).
 * Javier puede cambiar valores sin deploy: solo se reemplaza el JSON.
 *
 * Uso:
 *   [$pct, $prioridad, $detalle] = app(ScoringPitamex::class)->calificar($respuestas, completo: true);
 *
 * $respuestas = ['superficie' => '5 a 10 ha', 'infraestructura' => ['Bodega','Riego'], 'ubicacion' => ['lat'=>..,'lng'=>..], ...]
 */
class ScoringPitamex
{
    private array $cfg;

    public function __construct(?string $ruta = null)
    {
        // En resources/data para que se suba con git (storage/app está ignorado)
        $ruta ??= resource_path('data/scoring-pitamex.json');
        $this->cfg = json_decode(file_get_contents($ruta), true, flags: JSON_THROW_ON_ERROR);
    }

    /** @return array{0:int,1:string,2:array} [compatibilidad 0–100, 'A'|'B'|'C', detalle por pregunta] */
    public function calificar(array $r, bool $completo = true): array
    {
        $suma = 0.0;
        $pesos = 0.0;
        $detalle = [];

        foreach ($this->cfg['preguntas'] as $clave => $p) {
            $peso = $this->cfg['pesos'][$p['peso']];
            $valor = $this->valor($clave, $p, $r[$clave] ?? null);
            if ($valor === null) {
                continue;               // sin respuesta (u opción desconocida): no cuenta
            }
            $suma += $peso * $valor;
            $pesos += $peso;
            $detalle[$clave] = ['peso' => $peso, 'valor' => $valor];
        }

        $pct = $pesos > 0 ? (int) round($suma / $pesos * 100) : 0;

        return [$pct, $this->prioridad($pct, $r, $completo), $detalle];
    }

    private function valor(string $clave, array $p, mixed $resp): ?float
    {
        if ($resp === null || $resp === '' || $resp === []) {
            return null;
        }

        // Multi: cuántas de [Bodega, Tractor, Cercado, Riego] marcó / 4; "Ninguna" = 0
        if (($p['tipo'] ?? null) === 'multi') {
            $marcadas = (array) $resp;
            if (in_array('Ninguna', $marcadas, true)) {
                return 0.0;
            }
            $validas = array_intersect($marcadas, ['Bodega', 'Tractor', 'Cercado', 'Riego']);
            return count($validas) / 4;
        }

        // Ubicación: solo cuenta si la compartió
        if ($clave === 'ubicacion') {
            return (is_array($resp) && isset($resp['lat'], $resp['lng'])) ? (float) $p['valores']['compartida'] : null;
        }

        // Una respuesta que no es texto (p. ej. un arreglo) en una pregunta de opción única no cuenta
        if (! is_scalar($resp)) {
            logger()->warning('Scoring PITAMEX: respuesta con formato inválido', compact('clave'));
            return null;
        }

        if (!array_key_exists($resp, $p['valores'] ?? [])) {
            logger()->warning("Scoring PITAMEX: opción sin valor", compact('clave', 'resp'));
            return null;
        }

        return (float) $p['valores'][$resp];
    }

    private function prioridad(int $pct, array $r, bool $completo): string
    {
        if (!$completo || $pct < 50) {
            return 'C';
        }
        $pronto = in_array($r['iniciar'] ?? null, ['Próximo ciclo', 'Inmediatamente'], true)
            || ($r['etapa'] ?? null) === 'Avanzado';

        return ($pct >= 75 && $pronto) ? 'A' : 'B';
    }
}
