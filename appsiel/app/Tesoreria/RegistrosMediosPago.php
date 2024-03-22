<?php

namespace App\Tesoreria;

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
        
        //dd($filas_tabla_medios_recaudos,$lineas_registros_medios_recaudos,Input::get('id_transaccion'));
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

    // campo_lineas_recaudos is type JSON
    public function get_datos_ids( $campo_lineas_recaudos )
    {
        $datos = [];
        foreach( $campo_lineas_recaudos as $linea )
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
}
