<?php

namespace App\Exports;

use App\Models\Form;
use App\Models\TypeForm;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FormsMultiSheetExport implements WithMultipleSheets
{
    public $search;

    public function __construct($search)
    {
        $this->search = $search;
    }

    public function sheets(): array
    {
        $sheets = [];

        $types = TypeForm::get();

        $search = $this->search;

        foreach ($types as $type) {
            $formsQuery = Form::join('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
                ->select('forms.*', 'type_forms.name as type', 'type_forms.str as str')
                ->where('forms.type_form_id', $type->id);

            if ($search) {
                $formsQuery->where(function ($q) use ($search) {
                    $q->where('forms.name', 'like', "%{$search}%")
                        ->orWhere('type_forms.name', 'like', "%{$search}%")
                        ->orWhere('forms.form->phone_contact', 'like', "%{$search}%");
                });
            }

            $forms = $formsQuery->get();

            $sheets[] = new FormsExport($forms, $type->id);

        }

        return $sheets;

    }
}
