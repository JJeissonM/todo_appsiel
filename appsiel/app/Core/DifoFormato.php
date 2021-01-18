<?php

namespace App\Core;

use Auth;
use DB;
use Input;

use Illuminate\Database\Eloquent\Model;

class DifoFormato extends Model
{
    protected $fillable = [
        'core_app_id', 'descripcion', 'tipo', 'nota_mensaje',
        'maneja_firma_autorizada', 'maneja_curso', 'curso_predeterminado', 'maneja_periodo', 'periodo_predeterminado', 'maneja_anio', 'maneja_estudiantes', 'plantilla'
    ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Aplicación', 'Nombre', 'Tipo', 'Nota/Mensaje', 'Maneja Firma Autotizada', 'Curso Predeterminado', 'Plantilla', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = DifoFormato::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'difo_formatos.core_app_id')
            ->select(
                'sys_aplicaciones.descripcion AS campo1',
                'difo_formatos.descripcion AS campo2',
                'difo_formatos.tipo AS campo3',
                'difo_formatos.nota_mensaje AS campo4',
                'difo_formatos.maneja_firma_autorizada AS campo5',
                'difo_formatos.curso_predeterminado AS campo6',
                'difo_formatos.plantilla AS campo7',
                'difo_formatos.estado AS campo8',
                'difo_formatos.id AS campo9'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("difo_formatos.descripcion", "LIKE", "%$search%")
            ->orWhere("difo_formatos.tipo", "LIKE", "%$search%")
            ->orWhere("difo_formatos.nota_mensaje", "LIKE", "%$search%")
            ->orWhere("difo_formatos.maneja_firma_autorizada", "LIKE", "%$search%")
            ->orWhere("difo_formatos.curso_predeterminado", "LIKE", "%$search%")
            ->orWhere("difo_formatos.plantilla", "LIKE", "%$search%")
            ->orWhere("difo_formatos.estado", "LIKE", "%$search%")
            ->orderBy('difo_formatos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = DifoFormato::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'difo_formatos.core_app_id')
            ->select(
                'sys_aplicaciones.descripcion AS APLICACIÓN',
                'difo_formatos.descripcion AS NOMBRE',
                'difo_formatos.tipo AS TIPO',
                'difo_formatos.nota_mensaje AS NOTA_MENSAJE',
                'difo_formatos.maneja_firma_autorizada AS MANEJA_FIRMA_AUTOTIZADA',
                'difo_formatos.curso_predeterminado AS CURSO_PREDETERMINADO',
                'difo_formatos.plantilla AS PLANTILLA',
                'difo_formatos.estado AS ESTADO'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("difo_formatos.descripcion", "LIKE", "%$search%")
            ->orWhere("difo_formatos.tipo", "LIKE", "%$search%")
            ->orWhere("difo_formatos.nota_mensaje", "LIKE", "%$search%")
            ->orWhere("difo_formatos.maneja_firma_autorizada", "LIKE", "%$search%")
            ->orWhere("difo_formatos.curso_predeterminado", "LIKE", "%$search%")
            ->orWhere("difo_formatos.plantilla", "LIKE", "%$search%")
            ->orWhere("difo_formatos.estado", "LIKE", "%$search%")
            ->orderBy('difo_formatos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE FORMATOS";
    }

    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Bloques_eeff
    */

    public function secciones()
    {
        return $this->belongsToMany('App\Core\DifoSeccion', 'difo_secciones_formatos', 'id_formato', 'id_seccion');
    }


    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
        $encabezado_tabla = ['Orden', 'ID', 'Nombre', 'Contenido', 'Presentación', 'Alineación', 'Acción'];
        for ($i = 0; $i < count($encabezado_tabla); $i++) {
            $tabla .= '<th>' . $encabezado_tabla[$i] . '</th>';
        }
        $tabla .= '</thead>
                    <tbody>';
        foreach ($registros_asignados as $fila) {
            $orden = DB::table('difo_secciones_formatos')->where('id_seccion', '=', $fila['id'])
                ->where('id_formato', '=', $registro_modelo_padre->id)
                ->value('orden');

            $tabla .= '<tr>';
            $tabla .= '<td>' . $orden . '</td>';
            $tabla .= '<td>' . $fila['id'] . '</td>';
            $tabla .= '<td>' . $fila['descripcion'] . '</td>';
            $tabla .= '<td>' . $fila['contenido'] . '</td>';
            $tabla .= '<td>' . $fila['presentacion'] . '</td>';
            $tabla .= '<td>' . $fila['alineacion'] . '</td>';
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

    public static function get_opciones_modelo_relacionado($id_formato)
    {
        $vec[''] = '';

        $opciones = DB::table('difo_secciones')->get();
        foreach ($opciones as $opcion) {
            $esta = DB::table('difo_secciones_formatos')->where('id_formato', $id_formato)->where('id_seccion', $opcion->id)->get();

            if (empty($esta)) {
                $vec[$opcion->id] = $opcion->descripcion . " " . $opcion->contenido;
            }
        }
        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'difo_secciones_formatos';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'id_formato';
        $registro_modelo_hijo_id = 'id_seccion';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }
}
