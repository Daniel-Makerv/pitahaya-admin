<?php

namespace App\Exports;

use App\Models\TypeForm;
use Carbon\Carbon;
use App\Models\Form;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FormsExportV2Template implements WithMultipleSheets
{

    public function __construct() {}

    public function sheets(): array
    {
        $sheets = [];

        // Hoja general
        // $sheets[] = new FormsSheetExport();

        // Una hoja por cada tipo
        $types = TypeForm::whereIn('id', [7, 8, 9, 10, 11])->get();

        foreach ($types as $type) {
            $forms = Form::where('type_form_id', $type->id)->get();
            $sheets[] = new FormsExportV2(
                $forms,
                $type->id
            );
        }

        return $sheets;
    }
}
