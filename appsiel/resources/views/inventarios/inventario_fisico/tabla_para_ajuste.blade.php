<?php 
    $filas_tabla = '';

    foreach($doc_registros as $linea )
    {
        $ajustar = true;
        $cantidad_dif = $linea->cantidad - $linea->cantidad_sistema;

        // WARNING: MOTIVOS ASIGNADOS MANUALMENTE
        if ( $cantidad_dif > 0 )
        {
            $motivo = '<span style="color:white;">13-</span><span style="color:green;">Entrada (+) Sobrante</span><input type="hidden" class="movimiento" value="entrada">';
        }else{
            $motivo = '<span style="color:white;">14-</span><span style="color:red;">Salida (-) Faltante</span><input type="hidden" class="movimiento" value="salida">';
            $cantidad_dif *= -1;
        }

        $costo_total = $linea->costo_prom_sistema * $cantidad_dif;

        if (  (-0.0001 < $cantidad_dif) && ($cantidad_dif < 0.0001 ) )
        {
            $ajustar = false;
        }

        if ( $ajustar ) {
            $filas_tabla .= '<tr id="'.$linea->producto_id.'">
                        <td>'.$linea->producto_id.'</td>
                        <td class="nom_prod">'.$linea->producto_descripcion.'</td>
                        <td>'.$motivo.'</td>
                        <td>$'.number_format( $linea->costo_prom_sistema, 2, '.', '').'</td>
                        <td class="cantidad">'.number_format( $cantidad_dif, 2, '.', '').' '.$linea->unidad_medida1.'</td>
                        <td class="costo_total">$'.$costo_total.'</td>
                        <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button>
                        </td>
                    </tr>';
        } 
    }
    echo $filas_tabla;
?>