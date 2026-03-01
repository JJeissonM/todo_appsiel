<?php

namespace App\Compras;

use Collective\Html\FormFacade as Form;

class ComprasTransaccion
{
    public static function get_datos_tabla_ingreso_lineas_registros($tipo_transaccion, $motivos)
    {
        $campos_invisibles = app($tipo_transaccion->modelo_registros_documentos)->campos_invisibles_linea_registro;
        $campos_visibles = app($tipo_transaccion->modelo_registros_documentos)->campos_visibles_linea_registro;
        $aplica_retencion_fuente = in_array((int)$tipo_transaccion->id, [25, 48]);

        if (!$aplica_retencion_fuente) {
            $campos_invisibles = array_values(array_filter($campos_invisibles, function ($campo) {
                return !in_array($campo, ['contab_retencion_id', 'tasa_retencion', 'valor_retencion']);
            }));

            $campos_visibles = array_values(array_filter($campos_visibles, function ($campo) {
                return $campo[0] !== 'Ret. Fuente';
            }));
        }

        $fila_controles_formulario = '<tr id="linea_ingreso_default">';

        $columnas = [];
        $i = 0;
        foreach ($campos_invisibles as $value) {
            $fila_controles_formulario .= '<td style="display: none;"><div class="' . $value . '"></div></td>';
            $columnas[$i] = ['name' => $value, 'display' => 'none', 'etiqueta' => '', 'width' => ''];
            $i++;
        }

        foreach ($campos_visibles as $value) {
            $columnas[$i] = ['name' => '', 'display' => '', 'etiqueta' => $value[0], 'width' => $value[1]];
            $i++;
        }

        $columna_retencion = '';
        if ($aplica_retencion_fuente) {
            $columna_retencion = '<td> <select id="contab_retencion_id" class="form-control"><option value="0" data-tasa="0">Sin retención</option></select> </td>';
        }

        $fila_controles_formulario .= '<td> &nbsp; </td>
                        <td> 
                            ' . Form::text('inv_producto_id', null, ['id' => 'inv_producto_id', 'data-toggle' => 'tooltip', 'autocomplete' => 'off', 'title' => 'Presione dos veces ESC para terminar.']) . '
                            <div id="suggestions"></div>
                        </td>
                        <td> ' . Form::select('inv_motivo_id', $motivos, null, ['id' => 'inv_motivo_id']) . ' </td>
                        <td> ' . Form::text('existencia_actual', null, ['disabled' => 'disabled', 'id' => 'existencia_actual', 'width' => '15px']) . ' </td>
                        <td> ' . Form::text('cantidad', null, ['disabled' => 'disabled', 'id' => 'cantidad']) . ' </td>
                        <td> ' . Form::text('precio_unitario', null, ['id' => 'precio_unitario']) . Form::hidden('costo_unitario', null, ['disabled' => 'disabled', 'id' => 'costo_unitario']) . ' </td>
                        <td> ' . Form::text('tasa_descuento', null, ['id' => 'tasa_descuento', 'width' => '30px']) . ' </td>
                        <td> ' . Form::text('valor_unitario_descuento', null, ['id' => 'valor_unitario_descuento']) . Form::text('valor_total_descuento', null, ['id' => 'valor_total_descuento']) . ' </td>
                        ' . $columna_retencion . '
                        <td> ' . Form::text('tasa_impuesto', null, ['disabled' => 'disabled', 'id' => 'tasa_impuesto', 'width' => '15px']) . ' </td>
                        <td> ' . Form::text('precio_total', null, ['readonly' => 'readonly', 'id' => 'precio_total']) . Form::hidden('costo_total', null, ['disabled' => 'disabled', 'id' => 'costo_total']) . ' </td>
                        <td><button class="btn btn-xs btn-success" onclick="agregar_nueva_linea()" id="btn_agregar_nueva_linea"><i class="fa fa-check"></i></button></td>
                    </tr>';

        return [
            'titulo' => 'Líneas de registros',
            'columnas' => $columnas,
            'fila_body' => '',
            'fila_foot' => $fila_controles_formulario
        ];
    }
}
