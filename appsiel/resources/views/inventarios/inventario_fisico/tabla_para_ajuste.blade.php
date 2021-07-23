<?php 
    $filas_tabla = '';

    foreach($doc_registros as $linea )
    {
        $ajustar = true;
        $cantidad_dif = round( $linea->cantidad - $linea->cantidad_sistema , 2);

        // WARNING: MOTIVOS ASIGNADOS MANUALMENTE
        if ( $cantidad_dif > 0 )
        {
            $motivo = '<span style="color:white;">13-</span><span style="color:green;">Entrada (+) Sobrante</span><input type="hidden" class="movimiento" value="entrada">';
        }else{
            $motivo = '<span style="color:white;">14-</span><span style="color:red;">Salida (-) Faltante</span><input type="hidden" class="movimiento" value="salida">';
            $cantidad_dif *= -1;
        }

        $costo_total = $linea->costo_prom_sistema * $cantidad_dif;

        if (  (-1 < $cantidad_dif) && ($cantidad_dif < 1 ) )
        {
            $ajustar = false;
        }

        if ( $ajustar ) {

            $etiqueta_producto = $linea->producto_descripcion . ' (' . $linea->item->unidad_medida1 . ')';

            if ( $linea->item->unidad_medida2 != '')
            {
                $etiqueta_producto = $linea->producto_descripcion . ' (' . $linea->item->unidad_medida1 . ')' . ' - Talla: ' . $linea->item->unidad_medida2 . ')';
            }

            $filas_tabla .= '<tr id="'.$linea->producto_id.'">
                        <td style="display:none;">0</td>
                        <td class="text-center">'.$linea->producto_id.'</td>
                        <td class="nom_prod">'. $etiqueta_producto .'</td>
                        <td>'.$motivo.'</td>
                        <td class="text-right">$'.number_format( $linea->costo_prom_sistema, 2, '.', '').'</td>
                        <td class="text-center cantidad">'.number_format( $cantidad_dif, 2, '.', '').' '.$linea->unidad_medida1.'</td>
                        <td class="text-right costo_total">$'.$costo_total.'</td>
                        <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button>
                        </td>
                    </tr>';
        } 
    }
    echo $filas_tabla;
?>