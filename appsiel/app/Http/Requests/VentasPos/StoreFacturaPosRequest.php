<?php

namespace App\Http\Requests\VentasPos;

use App\Http\Requests\Request;

class StoreFacturaPosRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'url_id_modelo' => 'required|integer',
            'core_tipo_transaccion_id' => 'required|integer',
            'core_tipo_doc_app_id' => 'required|integer',
            'core_empresa_id' => 'required|integer',
            'fecha' => 'required|date',
            'cliente_id' => 'required|integer',
            'vendedor_id' => 'required|integer',
            'pdv_id' => 'required|integer',
            'cajero_id' => 'required|integer',
            'lineas_registros' => 'required|string',
            'lineas_registros_medios_recaudos' => 'required|string',
            'uniqid' => 'required|string|max:100',
            'request_id' => 'nullable|string|max:120',
            'draft_id' => 'nullable|string|max:120',
            'tab_instance_id' => 'nullable|string|max:120'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $lineas = json_decode((string)$this->input('lineas_registros'), true);
            if (!is_array($lineas) || empty($lineas)) {
                $validator->errors()->add('lineas_registros', 'Debe enviar líneas de productos válidas.');
            }

            $medios = json_decode((string)$this->input('lineas_registros_medios_recaudos'), true);
            if (!is_array($medios)) {
                $validator->errors()->add('lineas_registros_medios_recaudos', 'Debe enviar medios de recaudo válidos.');
                return;
            }

            foreach ($medios as $index => $linea) {
                if (!is_array($linea)) {
                    $validator->errors()->add('lineas_registros_medios_recaudos', 'Formato inválido en línea de medio de recaudo #' . ($index + 1));
                    break;
                }

                if (!array_key_exists('teso_medio_recaudo_id', $linea) || !array_key_exists('valor', $linea)) {
                    $validator->errors()->add('lineas_registros_medios_recaudos', 'Faltan campos requeridos en medios de recaudo.');
                    break;
                }
            }
        });
    }
}

