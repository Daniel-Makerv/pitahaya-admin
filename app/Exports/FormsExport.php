<?php

namespace App\Exports;

use App\Models\TypeForm;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FormsExport implements FromCollection, WithHeadings, WithTitle
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
            return json_decode($form->form, true);
        });
    }

    public function headings(): array
    {
        try {
            //code...
            $headers = TypeForm::find($this->typeId);
            return json_decode($headers->headers, true);

        } catch (\Exception $err) {
            //throw $th;
            dd($err->getMessage(). $this->typeId);
        }

    }

    public function title(): string
    {
        $title = TypeForm::find($this->typeId);
        return $title->name; // Nombre de la hoja
    }
}
