<?php

namespace App\Contabilidad;

use DB;
use Input;
use Form;

class ContabTransaccion
{
    
    public static function get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos = [] )
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
        $fila_controles_formulario .= '<td> 
                            '.Form::text('cuenta_id', null, ['class'=>'form-control autocompletar', 'id'=>'cuenta_id', 'data-toggle'=>'tooltip', 'autocomplete'=>'off', 'title'=>'Presione dos veces ESC para terminar.']) .'
                        </td>
                        <td> '. Form::text('tercero_id', null, ['class'=>'form-control autocompletar','id'=>'tercero_id', 'autocomplete'=>'off'] ) .'
                        </td>
                        <td> '. Form::text('detalle', null, ['class'=>'form-control','id'=>'detalle']) .' </td>
                        <td> '. Form::text('valor_db', null, ['class'=>'form-control','id'=>'valor_db']) .' </td>
                        <td> '. Form::text('valor_cr', null, ['class'=>'form-control','id'=>'valor_cr']) .' </td>
                        <td></td>
                    </tr>';

        // Línea de totales
        /**/
        $colspan = 3;
        $fila_foot = '<tr>
                        <td colspan="'.$colspan.'"> &nbsp; </td>
                        <td> <div id="total_debito"> $0 </div> </td>
                        <td> <div id="total_credito"> $0 </div> </td>
                        <td> <div id="sumas_iguales"> - </div> </td>
                    </tr>';          
        

        // name = data-override
         $datos = [
                    'titulo' => 'Líneas de registros',
                    'columnas' => $columnas,
                    'fila_body' => '',
                    'fila_foot' => $fila_controles_formulario.$fila_foot
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
