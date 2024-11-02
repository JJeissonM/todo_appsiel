<?php

namespace App\Compras;

use Collective\Html\FormFacade as Form;

class ComprasTransaccion
{
    
    public static function get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos )
    {
        // Los campos y columnas de la tabla se llaman según el Model
        $campos_invisibles = app($tipo_transaccion->modelo_registros_documentos)->campos_invisibles_linea_registro;
        $campos_visibles = app($tipo_transaccion->modelo_registros_documentos)->campos_visibles_linea_registro;

        // Esta fila tiene los controles para recoger los datos (es una especie de formulario)
        // Se crean columnas ocultas para recoger la información que será almacenada en la base de datos: IDs, valores sin formato y sin redondear 
        $fila_controles_formulario = '<tr id="linea_ingreso_default">';

        // Columnas ocultas
        $columnas = []; // $columnas se usa para crear los encabezados de la tabla
        $i = 0;
        foreach ($campos_invisibles as $key => $value)
        {
            $fila_controles_formulario .= '<td style="display: none;"><div class="'.$value.'"></div></td>';
            $columnas[$i] = [ 'name' => $value, 'display' => 'none', 'etiqueta' => '', 'width' => ''];
            $i++;
        }


        foreach ($campos_visibles as $key => $value)
        {
            $columnas[$i] = [ 'name' => '', 'display' => '', 'etiqueta' => $value[0], 'width' => $value[1]];
            $i++;
        }

        // Controles (Inputs)
        $fila_controles_formulario .= '<td> &nbsp; </td>
                        <td> 
                            '.Form::text('inv_producto_id', null, ['id'=>'inv_producto_id', 'data-toggle'=>'tooltip', 'autocomplete'=>'off', 'title'=>'Presione dos veces ESC para terminar.']) .'
                            <div id="suggestions"></div>
                        </td>
                        <td> '. Form::select('inv_motivo_id',$motivos,null,['id'=>'inv_motivo_id']) .' </td>
                        <td> '. Form::text('existencia_actual', null, ['disabled'=>'disabled','id'=>'existencia_actual','width'=>'15px']) .' </td>
                        <td> '. Form::text('cantidad', null, ['disabled'=>'disabled','id'=>'cantidad']) .' </td>
                        <td> '. Form::text('precio_unitario', null, ['id'=>'precio_unitario']) . Form::hidden('costo_unitario', null, ['disabled'=>'disabled','id'=>'costo_unitario']) .' </td>
                        <td> '. Form::text('tasa_descuento', null, [ 'id'=>'tasa_descuento','width'=>'30px']) .' </td>
                        <td> '. Form::text('valor_unitario_descuento', null, [ 'id'=>'valor_unitario_descuento']) . Form::text('valor_total_descuento', null, [ 'id'=>'valor_total_descuento' ]) .' </td>
                        <td> '. Form::text('tasa_impuesto', null, ['disabled'=>'disabled','id'=>'tasa_impuesto','width'=>'15px']) .' </td>
                        <td> '. Form::text('precio_total', null, ['readonly'=>'readonly','id'=>'precio_total']) . Form::hidden('costo_total', null, ['disabled'=>'disabled','id'=>'costo_total']) .' </td>
                        <td><button class="btn btn-xs btn-success" onclick="agregar_nueva_linea()" id="btn_agregar_nueva_linea"><i class="fa fa-check"></i></button></td>
                    </tr>';

        // Línea de totales
        /*
        $colspan = 18;
        $fila_foot = '<tr>
                        <td colspan="'.$colspan.'"> &nbsp; </td>
                        <td> <div id="total_cantidad"> 0 UND </div> </td>
                        <td> &nbsp; </td>
                        <td> </td>
                    </tr>';          
        */

        // name = data-override
         $datos = [
                    'titulo' => 'Líneas de registros',
                    'columnas' => $columnas,
                    'fila_body' => '',
                    'fila_foot' => $fila_controles_formulario
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
