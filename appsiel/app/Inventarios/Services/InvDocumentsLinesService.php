<?php 

namespace App\Inventarios\Services;

class InvDocumentsLinesService
{	
    public function preparar_array_lineas_registros( $bodega_id, $request_registros, $modo_ajuste )
    {
        $lineas_registros = json_decode( $request_registros );

        // Quitar primera línea
        array_shift( $lineas_registros );

        // Quitar las dos últimas líneas
        array_pop($lineas_registros);
        array_pop($lineas_registros);

        $cantidad = count($lineas_registros);
        for ($i = 0; $i < $cantidad; $i++)
        {
            $lineas_registros[$i]->inv_bodega_id = $bodega_id;
            $lineas_registros[$i]->inv_producto_id = $lineas_registros[$i]->inv_producto_id;
            $lineas_registros[$i]->inv_motivo_id = explode( "-", $lineas_registros[$i]->motivo )[0];
            $lineas_registros[$i]->costo_unitario = (float) substr($lineas_registros[$i]->costo_unitario, 1);
            $lineas_registros[$i]->cantidad = (float) substr($lineas_registros[$i]->cantidad, 0, strpos($lineas_registros[$i]->cantidad, " "));
            $lineas_registros[$i]->costo_total = (float) substr($lineas_registros[$i]->costo_total, 1);

            if (!is_null($modo_ajuste))
            {
                if ($modo_ajuste == 'solo_cantidad')
                {
                    $lineas_registros[$i]->costo_unitario = 0;
                    $lineas_registros[$i]->costo_total = 0;
                }
            }
        }

        return $lineas_registros;
    }
}