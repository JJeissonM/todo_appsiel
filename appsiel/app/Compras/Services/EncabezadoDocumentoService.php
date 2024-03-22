<?php

namespace App\Compras\Services;

class EncabezadoDocumentoService
{
    public static function get_total_documento_desde_lineas_registros( $lineas_registros )
    {
        $total_documento = 0;
        
        $cantidad_registros = count( $lineas_registros );
        
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $total_documento += (float)$lineas_registros[$i]->precio_total;
        }

        return $total_documento;
    }
}

