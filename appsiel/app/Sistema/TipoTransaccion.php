<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;

class TipoTransaccion extends Model
{
    protected $table = 'sys_tipos_transacciones';

    protected $fillable = ['core_app_id', 'core_modelo_id', 'descripcion', 'orden', 'modelo_encabezados_documentos', 'modelo_registros_documentos', 'modelo_movimientos', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'App', 'Descripción', 'Modelo CRUD', 'Model Encabezado Docs.', 'Model Registro Docs.', 'Model movimientos', 'Estado'];

    public function tipos_documentos()
    {
        return $this->belongsToMany('App\Core\TipoDocApp', 'core_transaccion_tiene_documento', 'core_tipo_transaccion_id', 'core_tipo_doc_id');
    }

    public function motivos()
    {
        return $this->hasMany('App\Core\Motivo', 'core_tipo_transaccion_id');
    }

    public function core_app()
    {
        return $this->belongsTo('App\Sistema\Aplicacion');
    }

    public function modelo()
    {
        return $this->belongsTo('App\Sistema\Modelo');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TipoTransaccion::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'sys_tipos_transacciones.core_app_id')
            ->leftJoin('sys_modelos', 'sys_modelos.id', '=', 'sys_tipos_transacciones.core_modelo_id')
            ->select(

                'sys_aplicaciones.descripcion AS campo1',
                'sys_tipos_transacciones.descripcion AS campo2',
                'sys_modelos.descripcion AS Model campo3',
                'sys_tipos_transacciones.modelo_encabezados_documentos AS campo4',
                'sys_tipos_transacciones.modelo_registros_documentos AS campo5',
                'sys_tipos_transacciones.modelo_movimientos AS campo6',
                'sys_tipos_transacciones.estado AS campo7',
                'sys_tipos_transacciones.id AS campo8'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_modelos.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.modelo_encabezados_documentos", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.modelo_registros_documentos", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.modelo_movimientos", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.estado", "LIKE", "%$search%")
            ->orderBy('sys_tipos_transacciones.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TipoTransaccion::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'sys_tipos_transacciones.core_app_id')
            ->leftJoin('sys_modelos', 'sys_modelos.id', '=', 'sys_tipos_transacciones.core_modelo_id')
            ->select(
                'sys_aplicaciones.descripcion AS APP',
                'sys_tipos_transacciones.descripcion AS DESCRIPCIÓN',
                'sys_modelos.descripcion AS MODELO_CRUD',
                'sys_tipos_transacciones.modelo_encabezados_documentos AS MODEL_ENCABEZADO_DOCS',
                'sys_tipos_transacciones.modelo_registros_documentos AS MODEL_REGISTRO_DOCS',
                'sys_tipos_transacciones.modelo_movimientos AS MODEL_MOVIMIENTOS',
                'sys_tipos_transacciones.estado AS ESTADO'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_modelos.descripcion", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.modelo_encabezados_documentos", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.modelo_registros_documentos", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.modelo_movimientos", "LIKE", "%$search%")
            ->orWhere("sys_tipos_transacciones.estado", "LIKE", "%$search%")
            ->orderBy('sys_tipos_transacciones.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DE TRANSACCIONES";
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoTransaccion::where('estado', 'Activo')->select('id', 'descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->id . ')';
        }

        return $vec;
    }


    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Tipo de Documento
    */
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
        $encabezado_tabla = ['Orden', 'ID', 'Descripción', 'Acción'];
        for ($i = 0; $i < count($encabezado_tabla); $i++) {
            $tabla .= '<th>' . $encabezado_tabla[$i] . '</th>';
        }
        $tabla .= '</thead>
                    <tbody>';
        foreach ($registros_asignados as $fila) {
            $orden = DB::table('core_transaccion_tiene_documento')
                ->where('core_tipo_transaccion_id', '=', $registro_modelo_padre->id)
                ->where('core_tipo_doc_id', '=', $fila['id'])
                ->value('orden');

            $tabla .= '<tr>';
            $tabla .= '<td>' . $orden . '</td>';
            $tabla .= '<td>' . $fila['id'] . '</td>';
            $tabla .= '<td>' . $fila['descripcion'] . '</td>';
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

    public static function get_opciones_modelo_relacionado($core_tipo_transaccion_id)
    {
        $vec[''] = '';
        $opciones = DB::table('core_tipos_docs_apps')->get();
        foreach ($opciones as $opcion) {
            $esta = DB::table('core_transaccion_tiene_documento')->where('core_tipo_transaccion_id', $core_tipo_transaccion_id)->where('core_tipo_doc_id', $opcion->id)->get();
            if (empty($esta)) {
                $vec[$opcion->id] = $opcion->prefijo . ' ' . $opcion->descripcion;
            }
        }

        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'core_transaccion_tiene_documento';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'core_tipo_transaccion_id';
        $registro_modelo_hijo_id = 'core_tipo_doc_id';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }
}
