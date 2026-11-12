<?php

namespace App\Exports;

use App\Models\Form;
use App\Models\TypeForm;
use App\Support\FormFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Una hoja de Excel con los registros de un type_form.
 *
 * Las columnas se arman con las preguntas que traen los registros, así funciona igual con:
 *  - registros viejos (llaves como name_complete, phone_contact...)
 *  - registros nuevos (llaves = texto de la pregunta: "¿Cuál es tu nombre?")
 */
class FormsExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    /** Llaves del registro de la PWA que no son respuestas */
    private const META_KEYS = ['id', 'fecha', 'send_api'];

    /** Nombres bonitos para las llaves de los registros viejos */
    private const LEGACY_LABELS = [
        'name_complete'          => 'Nombre',
        'phone_contact'          => 'Teléfono / WhatsApp',
        'email'                  => 'Correo',
        'country'                => 'País',
        'municipality_state'     => 'Municipio / Estado',
        'city_state'             => 'Ciudad / Estado',
        'bussiness_ranch'        => 'Empresa / Rancho',
        'cargo_puesto'           => 'Cargo / Puesto',
        'Load_position'          => 'Área principal',
        'land_type'              => 'Tipo de terreno',
        'available_surface_ha'   => 'Superficie (ha)',
        'water_availability'     => 'Disponibilidad de agua',
        'frost_zone'             => '¿Zona con heladas?',
        'has_electricity'        => '¿Tiene energía eléctrica?',
        'has_labor'              => '¿Cuenta con mano de obra?',
        'decision_level'         => 'Nivel de decisión',
        'why_experience_cultive' => '¿En qué cultivos tienes experiencia?',
        'estimated_start_time'   => 'Tiempo estimado para iniciar',
        'fruit_type'             => 'Tipo de fruta',
        'volume'                 => 'Volumen',
        'frequency'              => 'Frecuencia',
        'presentation'           => 'Presentación',
        'quality'                => 'Calidad',
        'delivery_place'         => 'Lugar de entrega',
        'freight'                => 'Flete',
        'transport'              => 'Transporte',
        'payment'                => 'Pago',
        'credit_days'            => 'Crédito',
        'start_time'             => 'Inicio',
        'years_of_cultivation'   => 'Años cultivo',
        'surface_ha'             => 'Superficie (ha)',
        'plants'                 => 'Plantas',
        'varieties'              => 'Variedades',
        'purpose'                => 'Propósito',
        'tutor_type'             => 'Tutor',
        'irrigation_type'        => 'Riego',
        'fertilization'          => 'Fertilización',
        'problems'               => 'Problemas',
        'crop_status'            => 'Estado cultivo',
        'production_status'      => 'Producción',
        'objective'              => 'Objetivo',
        'advisory_type'          => 'Tipo asesoría',
        'involvement'            => 'Involucramiento',
        'commitment'             => 'Compromiso',
        'visit'                  => 'Visita',
        'role'                   => 'Rol',
        'use'                    => 'Uso',
        'has_plant'              => 'Planta',
        'process_type'           => 'Proceso',
        'fruit_state'            => 'Estado fruta',
        'variety'                => 'Variedad',
        'industry_grade'         => 'Grado industria',
        'profile'                => 'Perfil',
        'interest'               => 'Interés',
        'contact_method'         => 'Contacto',
        'notes'                  => 'Notas',
    ];

    protected Collection $forms;

    protected string $sheetTitle;

    /** @var array<int, string> llaves de las respuestas, en orden de columna */
    protected array $columns = [];

    protected bool $prepared = false;

    /**
     * @param  Collection  $forms  registros de forms
     * @param  int|null  $typeId  type_form de la hoja (para el nombre de la hoja)
     * @param  string|null  $sheetTitle  nombre de hoja ya calculado (lo usa FormsMultiSheetExport)
     */
    public function __construct($forms, $typeId = null, ?string $sheetTitle = null)
    {
        $this->forms = collect($forms);
        $this->sheetTitle = self::sheetName(
            $sheetTitle ?? (TypeForm::whereKey($typeId)->value('name') ?? 'Formularios')
        );
    }

    /**
     * Consulta base de los reportes (la usan la ruta y el reporte de todas las hojas).
     */
    public static function query(?string $search = null, $typeId = null, ?string $from = null, ?string $to = null): Builder
    {
        $query = Form::query()
            ->leftJoin('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
            ->select('forms.*', 'type_forms.name as type', 'type_forms.str as str')
            ->orderBy('forms.id');

        return FormFilters::apply($query, $search, $typeId, $from, $to);
    }

    /**
     * Excel: máximo 31 caracteres y sin \ / ? * [ ] :
     */
    public static function sheetName(string $name): string
    {
        $clean = trim(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $name));

        return Str::limit($clean !== '' ? $clean : 'Hoja', 31, '');
    }

    public function collection(): Collection
    {
        $this->prepare();

        return $this->forms->map(function ($form) {
            $data = $this->decode($form->form);

            $row = [$this->formatDate($data['fecha'] ?? $form->created_at)];

            foreach ($this->columns as $key) {
                $row[] = $this->answerText($data[$key] ?? '');
            }

            return $row;
        });
    }

    public function headings(): array
    {
        $this->prepare();

        return ['Fecha', ...array_map(fn($key) => $this->label($key), $this->columns)];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }

    /**
     * Columnas = todas las preguntas que aparecen en los registros, en el orden en que se contestaron.
     */
    protected function prepare(): void
    {
        if ($this->prepared) {
            return;
        }

        $columns = [];

        foreach ($this->forms as $form) {
            foreach (array_keys($this->decode($form->form)) as $key) {
                if (! in_array($key, self::META_KEYS, true) && ! in_array($key, $columns, true)) {
                    $columns[] = $key;
                }
            }
        }

        $this->columns = $columns;
        $this->prepared = true;
    }

    protected function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        // Registros guardados con doble json_encode
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    protected function label(string $key): string
    {
        if (isset(self::LEGACY_LABELS[$key])) {
            return self::LEGACY_LABELS[$key];
        }

        // Otras llaves viejas (snake_case): "tipo_de_clima" -> "Tipo de clima"
        if (preg_match('/^[A-Za-z0-9_]+$/', $key) && (str_contains($key, '_') || ctype_lower($key[0]))) {
            return Str::ucfirst(str_replace('_', ' ', Str::lower($key)));
        }

        // Registros nuevos: la llave ya es el texto de la pregunta
        return $key;
    }

    protected function answerText(mixed $answer): string
    {
        if (is_array($answer)) {
            return implode(', ', array_map('strval', $answer));
        }

        if (is_bool($answer)) {
            return $answer ? 'Sí' : 'No';
        }

        return trim((string) $answer);
    }

    protected function formatDate(mixed $date): string
    {
        if (! $date) {
            return '';
        }

        try {
            return Carbon::parse($date)->timezone(config('app.timezone'))->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $date;
        }
    }
}
