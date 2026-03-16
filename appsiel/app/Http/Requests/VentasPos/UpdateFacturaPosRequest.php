<?php

namespace App\Http\Requests\VentasPos;

use App\Http\Requests\Request;

class UpdateFacturaPosRequest extends Request
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'fecha' => 'required|date',
            'descripcion' => 'nullable|string|max:255',
            'forma_pago' => 'required|string|max:30',
            'fecha_vencimiento' => 'required|date',
            'vendedor_id' => 'required|integer',
            'lineas_registros' => 'required|string',
            'lineas_registros_medios_recaudos' => 'required|string',
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
            }
        });
    }
}

