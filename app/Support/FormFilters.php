<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Filtros de los registros de forms (lista /forms y reportes de Excel).
 *
 * Fecha del registro:
 *  - "fecha" dentro del JSON: cuándo se contestó en la PWA (aunque se haya enviado después, sin internet)
 *  - si no la tiene, forms.created_at (cuándo llegó al servidor)
 */
class FormFilters
{
    /** Zona horaria en la que el usuario elige las fechas */
    public const TIMEZONE = 'America/Mexico_City';

    /**
     * Fechas a usar según la petición:
     *  - sin fechas            -> último año (de hoy hace un año a hoy)
     *  - range=all             -> sin filtro de fechas (todo)
     *  - from / to             -> lo que eligió el usuario
     *
     * @return array{from: ?string, to: ?string, range: ?string}
     */
    public static function dates(Request $request): array
    {
        if ($request->input('range') === 'all') {
            return ['from' => null, 'to' => null, 'range' => 'all'];
        }

        $from = $request->input('from');
        $to = $request->input('to');

        if (! $request->filled('from') && ! $request->filled('to')) {
            $today = Carbon::now(self::TIMEZONE);

            return [
                'from'  => $today->copy()->subYear()->format('Y-m-d'),
                'to'    => $today->format('Y-m-d'),
                'range' => null,
            ];
        }

        return ['from' => $from, 'to' => $to, 'range' => null];
    }

    public static function apply(
        Builder $query,
        ?string $search = null,
        $typeId = null,
        ?string $from = null,
        ?string $to = null,
    ): Builder {
        if ($typeId) {
            $query->where('forms.type_form_id', $typeId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('forms.name', 'like', "%{$search}%")
                    ->orWhere('type_forms.name', 'like', "%{$search}%")
                    // Busca en todas las respuestas (teléfono, correo, empresa...)
                    ->orWhere('forms.form', 'like', "%{$search}%");
            });
        }

        return self::applyDateRange($query, $from, $to);
    }

    /**
     * Rango de fechas inclusivo: del inicio de "desde" al final de "hasta" (hora de México).
     */
    public static function applyDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        [$start, $end] = self::range($from, $to);

        if (! $start && ! $end) {
            return $query;
        }

        // La fecha del JSON viene en UTC con formato ISO: 2026-11-06T18:30:00.000Z
        $fechaSql = "STR_TO_DATE(SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(forms.form, '$.fecha')), 1, 19), '%Y-%m-%dT%H:%i:%s')";

        // created_at se guarda en la zona horaria de la app
        $appTz = config('app.timezone');

        return $query->where(function ($q) use ($fechaSql, $start, $end, $appTz) {
            // Registros con "fecha" en el JSON
            $q->where(function ($q) use ($fechaSql, $start, $end) {
                $q->whereRaw("{$fechaSql} IS NOT NULL");

                if ($start) {
                    $q->whereRaw("{$fechaSql} >= ?", [$start->copy()->utc()->format('Y-m-d H:i:s')]);
                }
                if ($end) {
                    $q->whereRaw("{$fechaSql} <= ?", [$end->copy()->utc()->format('Y-m-d H:i:s')]);
                }
            })
                // Registros sin "fecha": se usa created_at
                ->orWhere(function ($q) use ($fechaSql, $start, $end, $appTz) {
                    $q->whereRaw("{$fechaSql} IS NULL");

                    if ($start) {
                        $q->where('forms.created_at', '>=', $start->copy()->timezone($appTz)->format('Y-m-d H:i:s'));
                    }
                    if ($end) {
                        $q->where('forms.created_at', '<=', $end->copy()->timezone($appTz)->format('Y-m-d H:i:s'));
                    }
                });
        });
    }

    /**
     * Convierte "2026-11-01" / "2026-11-12" en inicio y fin del día. Ignora fechas inválidas
     * y voltea el rango si "desde" es mayor que "hasta".
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    public static function range(?string $from, ?string $to): array
    {
        $start = self::parse($from)?->startOfDay();
        $end = self::parse($to)?->endOfDay();

        if ($start && $end && $start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    private static function parse(?string $date): ?Carbon
    {
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date, self::TIMEZONE);
        } catch (\Throwable) {
            return null;
        }
    }
}
