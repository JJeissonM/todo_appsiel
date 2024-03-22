<?php

namespace App\Compras\Services;

use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMotivo;

class TesoreriaService
{
    public function get_campo_lineas_recaudos($lineas_registros_medios_recaudo, $lineas_registros_originales){

        $lineas_registros_medios_recaudos = json_decode( $lineas_registros_medios_recaudo, true );

        $total_documento = (new EncabezadoDocumentoService())->get_total_documento_desde_lineas_registros( $lineas_registros_originales );

        if ( count($lineas_registros_medios_recaudos) <= 1 )
        {
            $teso_motivo_id = '1-Recaudo clientes';
            $teso_motivo = TesoMotivo::find((int)config('tesoreria.motivo_tesoreria_compras_contado'));
            if ($teso_motivo != null) {
                $teso_motivo_id = $teso_motivo->id . '-' . $teso_motivo->descripcion;
            }

            $teso_caja_id = '1-Caja general';
            $caja = TesoCaja::find((int)config('tesoreria.caja_default_id'));
            if ($caja != null) {
                $teso_caja_id = $caja->id . '-' . $caja->descripcion;
            }

            $lineas_registros_medios_recaudos = [[
                'teso_medio_recaudo_id' => '1-Efectivo',
                'teso_motivo_id' => $teso_motivo_id,
                'teso_caja_id' => $teso_caja_id,
                'teso_cuenta_bancaria_id' => '0-',
                'valor' => '$' . $total_documento
            ]];

            return json_decode( json_encode( $lineas_registros_medios_recaudos ) );
        }

        return (new RegistrosMediosPago())->depurar_tabla_registros_medios_recaudos( $lineas_registros_medios_recaudo, $total_documento );
    }
}

