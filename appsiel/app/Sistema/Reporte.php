<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;

class Reporte extends Model
{
    protected $table = 'sys_reportes';
    protected $fillable = ['descripcion', 'core_app_id', 'url_form_action', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Aplicación', 'Descripción', 'Url Form Action', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Reporte::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'sys_reportes.core_app_id')
            ->select(
                'sys_aplicaciones.descripcion AS campo1',
                'sys_reportes.descripcion AS campo2',
                'sys_reportes.url_form_action AS campo3',
                'sys_reportes.estado AS campo4',
                'sys_reportes.id AS campo5'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_reportes.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_reportes.url_form_action", "LIKE", "%$search%")
            ->orWhere("sys_reportes.estado", "LIKE", "%$search%")
            ->orderBy('sys_reportes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Reporte::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'sys_reportes.core_app_id')
            ->select(
                'sys_aplicaciones.descripcion AS Aplicación',
                'sys_reportes.descripcion AS Descripción',
                'sys_reportes.url_form_action AS Url Form Action',
                'sys_reportes.estado AS Estado'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_reportes.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_reportes.url_form_action", "LIKE", "%$search%")
            ->orWhere("sys_reportes.estado", "LIKE", "%$search%")
            ->orderBy('sys_reportes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE REPORTES";
    }

    public function campos()
    {
        return $this->belongsToMany('App\Sistema\Campo', 'sys_reporte_tiene_campos', 'core_reporte_id', 'core_campo_id');
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Campo
    */
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
        $encabezado_tabla = ['Orden', 'ID', 'Tipo', 'Name', 'Descripción', 'Opciones', 'Valor', 'Atributos', 'Requerido', 'Editable', 'Único', 'Acción'];
        for ($i = 0; $i < count($encabezado_tabla); $i++) {
            $tabla .= '<th>' . $encabezado_tabla[$i] . '</th>';
        }
        $tabla .= '</thead>
                    <tbody>';
        foreach ($registros_asignados as $fila) {
            $orden = DB::table('sys_reporte_tiene_campos')->where('core_campo_id', '=', $fila['id'])
                ->where('core_reporte_id', '=', $registro_modelo_padre->id)
                ->value('orden');

            $tabla .= '<tr>';
            $tabla .= '<td>' . $orden . '</td>';
            $tabla .= '<td>' . $fila['id'] . '</td>';
            $tabla .= '<td>' . $fila['tipo'] . '</td>';
            $tabla .= '<td>' . $fila['name'] . '</td>';
            $tabla .= '<td>' . $fila['descripcion'] . '</td>';
            $tabla .= '<td>' . $fila['opciones'] . '</td>';
            $tabla .= '<td>' . $fila['value'] . '</td>';
            $tabla .= '<td>' . $fila['atributos'] . '</td>';
            $tabla .= '<td>' . $fila['requerido'] . '</td>';
            $tabla .= '<td>' . $fila['editable'] . '</td>';
            $tabla .= '<td>' . $fila['unico'] . '</td>';
            $tabla .= '<td>
                                        <a class="btn btn-danger btn-sm" href="' . url('web/eliminar_asignacion/registro_modelo_hijo_id/' . $fila['id'] . '/registro_modelo_padre_id/' . $registro_modelo_padre->id . '/id_app/' . Input::get('id') . '/id_modelo_padre/' . Input::get('id_modelo')) . '"><i class="fa fa-btn fa-trash"></i> </a>
                                        </td>
                            </tr>';
        }
        $tabla .= '</tbody>
                </table>
            </div>';
        return $tabla;
    }

    public static function get_opciones_modelo_relacionado($core_reporte_id)
    {
        $vec[''] = '';
        $opciones = DB::table('sys_campos')->get();
        foreach ($opciones as $opcion) {
            $esta = DB::table('sys_reporte_tiene_campos')->where('core_reporte_id', $core_reporte_id)->where('core_campo_id', $opcion->id)->get();
            if (empty($esta)) {
                $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion . ' (' . $opcion->tipo . ')' . ' (' . $opcion->name . ')';
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'sys_reporte_tiene_campos';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'core_reporte_id';
        $registro_modelo_hijo_id = 'core_campo_id';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }

    /** FIN ***/
}
