<?php

namespace App\Tesoreria;

use App\Ventas\Services\TreasuryServices;
use Illuminate\Support\Facades\Input;

class RegistrosMediosPago
{
    public function depurar_tabla_registros_medios_recaudos( string $filas_tabla_medios_recaudos, $default_value = null )
    {
        // Conviertir en un array asociativo al strig: JSON,true
        $lineas_registros_medios_recaudos = json_decode( $filas_tabla_medios_recaudos, true );

        if ( !is_array( $lineas_registros_medios_recaudos ) )
        {
            return json_decode( '[]' );
        }

        // Eliminar ultimo elemento del array (totales de la tabla)
        array_pop( $lineas_registros_medios_recaudos );
        
        if(empty($lineas_registros_medios_recaudos))
        {
            $lineas_registros_medios_recaudos = [[
                'teso_medio_recaudo_id' => '1-Efectivo',
                'teso_motivo_id' => '1-Recaudo clientes',
                'teso_caja_id' => '1-Caja general',
                'teso_cuenta_bancaria_id' => '0-',
                'valor' => '$'.$default_value
            ]];
        }
        
        // Devolver en formato JSON
        return json_decode( json_encode( $lineas_registros_medios_recaudos ) );
    }

    // lineas_recaudos is type JSON
    public function get_datos_ids( $lineas_registros_medios_recaudo, $lineas_registros, $total_documento = null )
    {
        $lineas_recaudos = $this->get_lineas_recaudos($lineas_registros_medios_recaudo, $lineas_registros, $total_documento);

        $datos = [];
        foreach( $lineas_recaudos as $linea )
        {
            $aux = [];
            $aux['teso_medio_recaudo_id'] = (int)explode("-", $linea->teso_medio_recaudo_id)[0];
            $aux['teso_motivo_id'] = (int)explode("-", $linea->teso_motivo_id)[0];
            $aux['teso_caja_id'] = (int)explode("-", $linea->teso_caja_id)[0];
            $aux['teso_cuenta_bancaria_id'] = (int)explode("-", $linea->teso_cuenta_bancaria_id)[0];
            $aux['valor_recaudo'] = (float)substr($linea->valor, 1);

            $datos[] = $aux;
        }

        return $datos;
    }

    public function get_lineas_recaudos($lineas_registros_medios_recaudo, $lineas_registros_originales, $total_documento = null){
        
        $lineas_registros_medios_recaudos = json_decode( $lineas_registros_medios_recaudo, true );

        if ( $total_documento == null) {
            $total_documento = $this->get_total_documento_desde_lineas_registros( $lineas_registros_originales );
        }

        if ( count($lineas_registros_medios_recaudos) <= 1 )
        {
            $teso_motivo_id = '1-Recaudo clientes';
            $teso_motivo = TesoMotivo::find((int)config('tesoreria.motivo_tesoreria_ventas_contado'));

            //$teso_motivo = TesoMotivo::find((int)config('tesoreria.motivo_tesoreria_compras_contado'));

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

        return $this->depurar_tabla_registros_medios_recaudos( $lineas_registros_medios_recaudo, $total_documento );
    }
    
    public function get_total_documento_desde_lineas_registros( array $lineas_registros )
    {
        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);
        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            $total_documento += (float)$lineas_registros[$i]->precio_total;
        } // Fin por cada registro

        return $total_documento;        
    }
    
}
