<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;

use App\Cuestionarios\Pregunta;
use App\Cuestionarios\CuestionarioTienePregunta;


class Cuestionario extends Model
{
    protected $table = 'sga_cuestionarios';

    protected $fillable = ['colegio_id', 'descripcion', 'detalle', 'activar_resultados', 'estado', 'created_by'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        return Cuestionario::where('created_by', Auth::user()->id)
            ->select('sga_cuestionarios.descripcion AS campo1', 'sga_cuestionarios.estado AS campo2', 'sga_cuestionarios.id AS campo3')
            ->orderBy('sga_cuestionarios.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/actividades_escolares/cuestionarios.js';

    public function preguntas()
    {
        return $this->belongsToMany('App\Cuestionarios\Pregunta', 'sga_cuestionario_tiene_preguntas');
    }


    /*
        FUNCIONES relativas al modelo relacionado a este modelo, en este caso Campo
    */
    public static function get_tabla($registro_modelo_padre, $registros_asignados)
    {
        $tabla = '<div class="table-responsive">
                <table class="table table-bordered table-striped" id="myTable">
                    <thead>';
        $encabezado_tabla = ['Orden', 'ID', 'Descripción', 'Tipo', 'Opciones', 'Opción correcta', 'Estado', 'Acción'];
        for ($i = 0; $i < count($encabezado_tabla); $i++) {
            $tabla .= '<th>' . $encabezado_tabla[$i] . '</th>';
        }
        $tabla .= '</thead>
                    <tbody>';
        foreach ($registros_asignados as $fila) {
            $orden = DB::table('sga_cuestionario_tiene_preguntas')
                ->where('cuestionario_id', '=', $registro_modelo_padre->id)
                ->where('pregunta_id', '=', $fila['id'])
                ->value('orden');

            $tabla .= '<tr>';
            $tabla .= '<td>' . $orden . '</td>';
            $tabla .= '<td>' . $fila['id'] . '</td>';
            $tabla .= '<td>' . $fila['descripcion'] . '</td>';
            $tabla .= '<td>' . $fila['tipo'] . '</td>';
            $tabla .= '<td>' . $fila['opciones'] . '</td>';
            $tabla .= '<td>' . $fila['respuesta_correcta'] . '</td>';
            $tabla .= '<td>' . $fila['estado'] . '</td>';
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

    public static function get_opciones_modelo_relacionado($cuestionario_id)
    {
        $vec[''] = '';
        $opciones = Pregunta::where('created_by', Auth::user()->id)
            ->get();
        foreach ($opciones as $opcion) {
            $esta = CuestionarioTienePregunta::where('cuestionario_id', $cuestionario_id)->where('pregunta_id', $opcion->id)->get();

            if (empty($esta->toArray())) {
                $vec[$opcion->id] = $opcion->descripcion;
            }
        }

        return $vec;
    }

    public static function get_datos_asignacion()
    {
        $nombre_tabla = 'sga_cuestionario_tiene_preguntas';
        $nombre_columna1 = 'orden';
        $registro_modelo_padre_id = 'cuestionario_id';
        $registro_modelo_hijo_id = 'pregunta_id';

        return compact('nombre_tabla', 'nombre_columna1', 'registro_modelo_padre_id', 'registro_modelo_hijo_id');
    }


    public static function opciones_campo_select()
    {
        $opciones = Cuestionario::where('created_by', Auth::user()->id)->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
