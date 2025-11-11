<?php

namespace App\Exports;

use App\Models\TypeForm;
use Carbon\Carbon;
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
            $dataBody = [];
            $data = json_decode($form->form, true);

            switch ($this->typeId) {
                case 1:
                    dd($data);
                    $dataBody = [
                        'fecha' => Carbon::parse($data['fecha'])->format('d/m/Y'),
                        'nombre' => $data['name_complete'],
                        'Teléfono / WhatsApp' => $data['phone_contact'],
                        'Municipio / Estado' => $data['municipality_state'],
                        'Tipo de terreno' => $data['land_type'],
                        'Superficie (ha)' => $data['available_surface_ha'],
                        'Disponibilidad de agua' => $data['water_availability'],
                        '¿Zona con heladas?' => $data['frost_zone'],
                        '¿Tiene energía eléctrica?' => $data['has_electricity'],
                        '¿Cuenta con mano de obra?' => $data['has_labor'],
                        'Nivel de decisión' => $data['decision_level'],
                        '¿en qué cultivos tienes experiencia?' => $data['why_experience_cultive'],
                        'Tiempo estimado para iniciar' => $data['estimated_start_time'],
                    ];
                    break;
            }

            return $dataBody;

        });
    }

    public function headings(): array
    {
        try {
            // code...
            $headers = TypeForm::find($this->typeId);

            return json_decode($headers->headers, true);

        } catch (\Exception $err) {
            // throw $th;
            dd($err->getMessage().$this->typeId);
        }

    }

    public function title(): string
    {
        $title = TypeForm::find($this->typeId);

        return $title->name; // Nombre de la hoja
    }
}
