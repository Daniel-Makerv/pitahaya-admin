<?php

namespace App\Exports;

use App\Models\TypeForm;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class FormsExportV2 implements FromCollection, WithHeadings, WithTitle
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
        // return $this->forms;
        return $this->forms->map(function ($form) {
            $dataBody = [];
            $data = json_decode($form->form, true);

            switch ($this->typeId) {
                case 7:
                    $dataBody = [
                        'Nombre completo' => $data['name_complete'],
                        'Empresa/Rancho' => $data['bussiness_ranch'],
                        'Cargo/Puesto' => $data['cargo_puesto'],
                        'Área principal' => $data['q1'],
                        '2.-Teléfono / WhatsApp' => $data['phone_contact'],
                        '2.-Correo' => $data['email'],
                        '2.-País' => $data['country'],
                        '3.- Estado/Ciudad' => $data['municipality_state'],
                        'Producción anual' => $data['produccion_anual'],
                        'Semanas de producción' => $data['semanas_de_produccion'],
                        'Variedades principales' => $data['variedades_principales'],
                        'Tipo de clima' => $data['tipo_de_clima'],
                        'Grados Brix' => $data['grados_brix'],
                        '¿Cuenta con certificaciones?' => $data['cuenta_con_certificaciones'],
                        '¿Le interesa producción limpia/libre de pesticidas?' => $data['le_interesa_produccion_limpia_libre_de_pesticidas'],
                        '¿Cuenta con cadena de frío?' => $data['cuenta_con_cadena_de_frio'],
                        '¿Busca relación de largo plazo?' => $data['busca_relacion_de_largo_plazo'],
                    ];
                    break;
                case 8:
                    $dataBody = [
                        'Nombre completo' => $data['name_complete'],
                        'Empresa/Rancho' => $data['bussiness_ranch'],
                        'Cargo/Puesto' => $data['cargo_puesto'],
                        'Área principal' => $data['q1'],
                        '2.-Teléfono / WhatsApp' => $data['phone_contact'],
                        '2.-Correo' => $data['email'],
                        '2.-País' => $data['country'],
                        '3.- Estado/Ciudad' => $data['municipality_state'],
                        'Área principal' => $data['Load_position'],
                        'Canal principal' => $data['canal_principal'],
                        'Volumen requerido' => $data['volumen_requerido'],
                        'Variedades de interés' => $data['variedades_de_interes'],
                        'Presentación deseada' => $data['presentacion_deseada'],
                        '¿Qué certificaciones requiere?' => $data['que_certificaciones_requiere'],
                        '¿Qué documentos solicita?' => $data['que_documentos_solicita'],
                        'Método de pago' => $data['metodo_de_pago'],
                        'Tiempo de pago' => $data['tiempo_de_pago'],
                        '¿Busca proveedor permanente?' => $data['busca_proveedor_permanente'],
                    ];
                    break;
                case 9:
                    $dataBody = [
                        'Nombre completo' => $data['name_complete'],
                        'Empresa/Rancho' => $data['bussiness_ranch'],
                        'Cargo/Puesto' => $data['cargo_puesto'],
                        'Área principal' => $data['q1'],
                        '2.-Teléfono / WhatsApp' => $data['phone_contact'],
                        '2.-Correo' => $data['email'],
                        '2.-País' => $data['country'],
                        '3.- Estado/Ciudad' => $data['municipality_state'],
                        '¿Qué necesita?' => $data['que_necesita'],
                        '¿Cuenta con huerta establecida?' => $data['cuenta_con_huerta_establecida'],
                        'Superficie' => $data['superficie'],
                    ];
                    break;
                case 10:
                    $dataBody = [
                        'Nombre completo' => $data['name_complete'],
                        'Empresa/Rancho' => $data['bussiness_ranch'],
                        'Cargo/Puesto' => $data['cargo_puesto'],
                        'Área principal' => $data['q1'],
                        '2.-Teléfono / WhatsApp' => $data['phone_contact'],
                        '2.-Correo' => $data['email'],
                        '2.-País' => $data['country'],
                        '3.- Estado/Ciudad' => $data['municipality_state'],
                        'Cargo/Puesto' => $data['Load_position'],
                        '¿Qué le interesa?' => $data['que_le_interesa'],
                        'Volumen requerido' => $data['volumen_requerido'],
                        '¿Requiere certificaciones?' => $data['requiere_certificaciones'],
                    ];
                    break;
                case 11:
                    $dataBody = [
                        'Nombre completo' => $data['name_complete'],
                        'Empresa/Rancho' => $data['bussiness_ranch'],
                        'Cargo/Puesto' => $data['cargo_puesto'],
                        'Área principal' => $data['q1'],
                        '2.-Teléfono / WhatsApp' => $data['phone_contact'],
                        '2.-Correo' => $data['email'],
                        '2.-País' => $data['country'],
                        '3.- Estado/Ciudad' => $data['municipality_state'],
                        'Tipo de colaboración' => $data['tipo_de_colaboracion'],
                        '¿Qué busca aportar?' => $data['que_busca_aportar'],
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
            dd($err->getMessage() . $this->typeId);
        }
    }

    public function title(): string
    {
        $title = TypeForm::find($this->typeId);

        return $title->name; // Nombre de la hoja
    }
}
