<?php

namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Nomina\NomContrato;
use App\Nomina\Services\Cotizante51Service;
use Illuminate\Http\Request;

class Cotizante51Controller extends Controller
{
    public function actualizar_dias_laborados(Request $request, $contrato_id)
    {
        $contrato = NomContrato::findOrFail((int)$contrato_id);
        $diasLaboradosMes = (int)$request->get('dias_laborados_mes', 0);

        $cotizante51Service = new Cotizante51Service();
        if ( !$cotizante51Service->esCotizante51($contrato) )
        {
            return response()->json([
                'message' => 'El contrato no corresponde a tipo cotizante 51.'
            ], 422);
        }

        if ( $diasLaboradosMes < 0 || $diasLaboradosMes > 30 )
        {
            return response()->json([
                'message' => 'El campo dias_laborados_mes debe estar entre 0 y 30.'
            ], 422);
        }

        $contrato->dias_laborados_mes = $diasLaboradosMes;
        $contrato->save();

        return response()->json([
            'message' => 'Días laborados actualizados correctamente.',
            'contrato_id' => $contrato->id,
            'dias_laborados_mes' => $contrato->dias_laborados_mes
        ]);
    }
}
