<?php

namespace App\Inventarios;

use DB;
use Input;
use Form;

class InvTransaccion
{
    
    public static function get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos )
    {
        // Los campos y columnas de la tabla se llaman según el Model InvDocRegistro
        $campos_invisibles = app($tipo_transaccion->modelo_registros_documentos)->campos_invisibles_linea_registro;
        $campos_visibles = app($tipo_transaccion->modelo_registros_documentos)->campos_visibles_linea_registro;

        // Esta fila tiene los controles para recoger los datos (es una especie de formulario)
        // Se crean columnas ocultas para recoger la información que será almacenada en la base de datos: IDs, valores sin formato y sin redondear 
        $fila_body = '<tr id="linea_ingreso_default">';

        // Columnas ocultas
        $columnas = [];
        $i = 0;
        foreach ($campos_invisibles as $key => $value)
        {
            $fila_body .= '<td style="display: none;"><div class="'.$value.'"></div></td>';
            $columnas[$i] = [ 'name' => $value, 'display' => 'none', 'etiqueta' => '', 'width' => ''];
            $i++;
        }


        foreach ($campos_visibles as $key => $value)
        {
            $columnas[$i] = [ 'name' => '', 'display' => '', 'etiqueta' => $value[0], 'width' => $value[1]];
            $i++;
        }

        // Controles (Inputs)
        $fila_body .= '<td> <label class="checkbox-inline" title="Activar ingreso por código de barras"><input type="checkbox" id="modo_ingreso" name="modo_ingreso" value="true" checked="checked"><i class="fa fa-barcode"></i></label> </td>
                        <td> 
                            '.Form::text('inv_producto_id', null, ['id'=>'inv_producto_id']) .'
                            <div id="suggestions"></div>
                        </td>
                        <td> '. Form::select('inv_motivo_id',$motivos,null,['id'=>'inv_motivo_id']) .' </td>
                        <td> '. Form::text('existencia_actual', null, ['disabled'=>'disabled','id'=>'existencia_actual']) .' </td>
                        <td> '. Form::text('costo_unitario', null, ['disabled'=>'disabled','id'=>'costo_unitario']) .' </td>
                        <td> '. Form::text('cantidad', null, ['disabled'=>'disabled','id'=>'cantidad']) .' </td>
                        <td> '. Form::text('costo_total', null, ['disabled'=>'disabled','id'=>'costo_total']) .' </td>
                        <td></td>
                    </tr>';

        // Línea de totales
        $colspan = 11;
        $fila_foot = '<tr>
                        <td colspan="'.$colspan.'">&nbsp;</td>
                        <td> <div id="lbl_total_cantidad"> 0 </div> </td>
                        <td> <div id="lbl_total_costo_total"> $0</div> </td>
                        <td> </td>
                    </tr>';          

        // name = data-override
         $datos = [
                    'titulo' => 'Líneas de registros',
                    'columnas' => $columnas,
                    'fila_body' => $fila_body,
                    'fila_foot' => $fila_foot
                ];

        switch ( $tipo_transaccion->id ) {
            case '1': // Entrada de almacén
                # code...
                break;
            case '2': // Transferencia
                # code...
                break;
            case '3': // Salida de inventario
                # code...
                break;
            
            default:
                # code...
                break;
        }

        return $datos;
    }
    
}
