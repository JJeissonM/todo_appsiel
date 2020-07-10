<?php

namespace App\VentasPos;

use DB;
use Input;
use Form;

class PreparaTransaccion
{
    
    public static function get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos, $body = '' )
    {
        // Los campos y columnas de la tabla se llaman según el Model InvDocRegistro
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

        /*
                            Form::text( 'inv_producto_id_aux_2', '', [ 'class' => 'form-control text_input_sugerencias', 'id' => 'inv_producto_id_aux', 'data-url_busqueda' => url('inv_consultar_productos_v2'), 'autocomplete'  => 'off' ] ) . Form::hidden( 'inv_producto_id', null, [ 'id' => 'inv_producto_id' ] )
        */
        
        $fila_controles_formulario .= '</tr>';

        // name = data-override
         $datos = [
                    'titulo' => 'Líneas de registros',
                    'columnas' => $columnas,
                    'fila_body' => $body,
                    'fila_foot' => '' //$fila_controles_formulario
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
