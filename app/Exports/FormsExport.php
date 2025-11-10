<?php

namespace App\Exports;

use App\Models\TypeForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FormsExport implements FromCollection, WithHeadings
{
    protected $forms;

    protected $typeId;

    public function __construct($forms, $typeId)
    {
        $this->forms = $forms;
        $this->typeId = $typeId;
    }

    public function collection()
    {
        return $this->forms->map(function ($form) {
            $data = json_decode($form->form, true);

            return [
                'Nombre' => $form->name,
                'Contacto' => $data['phone_contact'] ?? '',
                'Tipo' => $form->type,
            ];
        });
    }

    public function headings(): array
    {

        $headers = TypeForm::find($this->typeId);
        return  json_decode($headers->headers, true);
    }
}
