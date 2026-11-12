<?php

namespace App\Exports;

use App\Models\TypeForm;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Reporte "Todos": una hoja por type_form que tenga registros.
 */
class FormsMultiSheetExport implements WithMultipleSheets
{
    public function __construct(
        public ?string $search = null,
        public ?string $from = null,
        public ?string $to = null,
    ) {
    }

    public function sheets(): array
    {
        $sheets = [];
        $usedNames = [];

        foreach (TypeForm::orderBy('name')->get() as $type) {
            $forms = FormsExport::query($this->search, $type->id, $this->from, $this->to)->get();

            // Sin registros no se agrega hoja (antes salían hojas vacías)
            if ($forms->isEmpty()) {
                continue;
            }

            $sheets[] = new FormsExport($forms, $type->id, $this->uniqueName($type->name, $usedNames));
        }

        // Excel no permite un archivo sin hojas
        if (empty($sheets)) {
            $sheets[] = new FormsExport(collect(), null, 'Sin registros');
        }

        return $sheets;
    }

    /**
     * Dos type_forms pueden quedar con el mismo nombre al recortar a 31 caracteres.
     */
    private function uniqueName(string $name, array &$usedNames): string
    {
        $base = FormsExport::sheetName($name);
        $candidate = $base;
        $n = 2;

        while (in_array(mb_strtolower($candidate), $usedNames, true)) {
            $suffix = " ({$n})";
            $candidate = mb_substr($base, 0, 31 - mb_strlen($suffix)).$suffix;
            $n++;
        }

        $usedNames[] = mb_strtolower($candidate);

        return $candidate;
    }
}
