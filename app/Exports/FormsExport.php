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
                case 2:
                    $dataBody = [
                        'fecha' => Carbon::parse($data['fecha'])->format('d/m/Y'),
                        'nombre' => $data['name_complete'],
                        'Teléfono / WhatsApp' => $data['phone_contact'],
                        'Municipio / Estado' => $data['city_state'],
                        'Tipo de fruta' => $data['fruit_type'],
                        'Volumen' => $data['volume'],
                        'Frecuencia' => $data['frequency'],
                        'Presentación' => $data['presentation'],
                        'Calidad' => $data['quality'],
                        'Lugar de entrega' => $data['delivery_place'],
                        'Flete' => $data['freight'],
                        'Transporte' => $data['transport'],
                        'Pago' => $data['payment'],
                        'Crédito' => $data['credit_days'],
                        'Inicio' => $data['start_time'],
                    ];
                    break;
                case 3:
                    $dataBody = [
                        'fecha' => Carbon::parse($data['fecha'])->format('d/m/Y'),
                        'nombre' => $data['name_complete'],
                        'Teléfono / WhatsApp' => $data['phone_contact'],
                        'Municipio / Estado' => $data['municipality_state'],
                        'Años cultivo' => $data['years_of_cultivation'],
                        'Superficie(ha)' => $data['surface_ha'],
                        'Plantas' => $data['plants'],
                        'Variedades' => $data['varieties'],
                        'Propósito' => $data['purpose'],
                        'Tutor' => $data['tutor_type'],
                        'Riego' => $data['irrigation_type'],
                        'Fertilización' => $data['fertilization'],
                        'Problemas' => $data['problems'],
                        'Estado cultivo' => $data['crop_status'],
                        'Producción' => $data['production_status'],
                        'Objetivo' => $data['objective'],
                        'Tipo asesoría' => $data['advisory_type'],
                        'Involucramiento' => $data['involvement'],
                        'Compromiso' => $data['commitment'],
                        'Visita' => $data['visit'],
                    ];
                    break;
                case 4:
                    $dataBody = [
                        'fecha' => Carbon::parse($data['fecha'])->format('d/m/Y'),
                        'nombre' => $data['name_complete'],
                        'Teléfono / WhatsApp' => $data['phone_contact'],
                        'Municipio / Estado' => $data['city_state'],
                        'Rol' => $data['role'],
                        'Uso' => $data['use'],
                        'Planta' => $data['has_plant'],
                        'Proceso' => $data['process_type'],
                        'Volumen' => $data['volume'],
                        'Frecuencia' => $data['frequency'],
                        'Estado fruta' => $data['fruit_state'],
                        'Variedad' => $data['variety'],
                        'Grado industria' => $data['industry_grade'],
                        'Lugar entrega' => $data['delivery_place'],
                        'Flete' => $data['freight'],
                        'Pago' => $data['payment'],
                        'Crédito' => $data['credit_days'],
                        'Inicio' => $data['start_time'],
                    ];
                    break;
                case 5:
                    $dataBody = [
                        'fecha' => Carbon::parse($data['fecha'])->format('d/m/Y'),
                        'nombre' => $data['name_complete'],
                        'Teléfono / WhatsApp' => $data['phone_contact'],
                        'Municipio / Estado' => $data['municipality_state'],
                        'Perfil' => $data['profile'],
                        'Interés' => $data['interest'],
                        'Contacto' => $data['contact_method'],
                    ];
                    break;
                case 6:
                    dd($data);
                    $dataBody = [
                        'fecha' => Carbon::parse($data['fecha'])->format('d/m/Y'),
                        'nombre' => $data['name_complete'],
                        'Teléfono / WhatsApp' => $data['phone_contact'],
                        'Municipio / Estado' => $data['municipality_state'],
                        'Notas' => $data['notes'],
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
