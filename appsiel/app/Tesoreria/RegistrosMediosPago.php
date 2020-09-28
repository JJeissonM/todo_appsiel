<?php

namespace App\Tesoreria;

class RegistrosMediosPago
{
    public function formatear_tabla_registros_medios_recaudos( string $filas_tabla_medios_recaudos )
    {
        // Conviertir en un array asociativo al strig: JSON,true
        $lineas_registros_medios_recaudos = json_decode( $filas_tabla_medios_recaudos, true ); 

        // Eliminar ultimo elemento del array (totales de la tabla)
        $aux = array_pop( $lineas_registros_medios_recaudos ); 

        // Devolver en formato JSON
        return json_decode( json_encode( $lineas_registros_medios_recaudos ) );
    }
}
