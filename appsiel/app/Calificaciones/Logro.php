<?php

namespace App\Calificaciones;

use App\Calificaciones\Calificacion;
use Illuminate\Database\Eloquent\Model;

use App\Calificaciones\CursoTieneAsignatura;

use DB;
use App\Calificaciones\Periodo;
use App\Matriculas\PeriodoLectivo;

class Logro extends Model
{
    protected $table = 'sga_logros';
    
    protected $fillable = ['id_colegio','codigo','asignatura_id', 'descripcion','estado','ocupado','escala_valoracion_id','curso_id','periodo_id'];

    public $encabezado_tabla = ['Cód.','Año lectivo','Periodo','Curso','Asignatura','Escala de valoración','Descripción','Estado','Acción'];


    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/calificaciones/logros.js';

    // [index, create, edit, show, ]
    public $vistas = '{ 
                        "index":layouts.index,
                        "create":"layouts.create",
                        "edit":null,
                        "show":null
                    }';

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","store":"calificaciones_logros","update":"calificaciones_logros/id_fila","cambiar_estado":"a_i/id_fila","eliminar":"calificaciones_eliminar_logro/id_fila"}';



    /**
     * Obtener cada logro que pertenece a la calificación.
     */
    public function calificacion()
    {
        return $this->belongsTo(Calificacion::class);
    }


    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")") AS campo6';

        $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        //$periodos_actuales = Periodo::get_activos_periodo_lectivo( $periodo_lectivo_id );

        $registros = Logro::leftJoin('sga_periodos','sga_periodos.id','=','sga_logros.periodo_id')
                        ->leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
                        ->leftJoin('sga_cursos','sga_cursos.id','=','sga_logros.curso_id')
                        ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_logros.asignatura_id')
                        ->leftJoin('sga_escala_valoracion','sga_escala_valoracion.id','=','sga_logros.escala_valoracion_id')
                        ->where('sga_periodos.periodo_lectivo_id',$periodo_lectivo_id)
                        ->where('sga_logros.escala_valoracion_id','<>',0)
                        ->select('sga_logros.codigo AS campo1',
                                'sga_periodos_lectivos.descripcion AS campo2',
                                'sga_periodos.descripcion AS campo3',
                                'sga_cursos.descripcion AS campo4',
                                'sga_asignaturas.descripcion AS campo5',
                                DB::raw($select_raw),
                                'sga_logros.descripcion AS campo7',
                                'sga_logros.estado AS campo8',
                                'sga_logros.id AS campo9')
                        ->get()
                        ->toArray();

        return $registros;
    }



    public static function get_logros($id_colegio, $curso_id, $asignatura_id, $periodo_id = null)
    {

        $array_wheres = ['sga_logros.id_colegio' => $id_colegio];

        if ( $curso_id != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_logros.curso_id' => $curso_id]);
        }

        if ( $asignatura_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_logros.asignatura_id' => $asignatura_id] );
        }

        if ( $periodo_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_logros.periodo_id' => $periodo_id] );
        }
        
        $select_raw = 'CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")") AS campo6';

        $registros = Logro::where($array_wheres)
                        ->where('sga_logros.escala_valoracion_id','<>',0)
                        ->leftJoin('sga_periodos','sga_periodos.id','=','sga_logros.periodo_id')
                        ->leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_periodos.periodo_lectivo_id')
                        ->leftJoin('sga_cursos','sga_cursos.id','=','sga_logros.curso_id')
                        ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_logros.asignatura_id')
                        ->leftJoin('sga_escala_valoracion','sga_escala_valoracion.id','=','sga_logros.escala_valoracion_id')
                        ->select('sga_logros.codigo AS campo1',
                                'sga_periodos_lectivos.descripcion AS campo2',
                                'sga_periodos.descripcion AS campo3',
                                'sga_cursos.descripcion AS campo4',
                                'sga_asignaturas.descripcion AS campo5',
                                DB::raw($select_raw),
                                'sga_logros.descripcion AS campo7',
                                'sga_logros.estado AS campo8',
                                'sga_logros.id AS campo9')
                        ->orderBy('sga_logros.codigo','DESC')
                        ->get()
                        ->toArray();

        return $registros;
    }

    public static function get_logros_periodo_curso_asignatura( $periodo_id, $curso_id, $asignatura_id)
    {

        $array_wheres = [];

        $array_wheres = array_merge($array_wheres, ['sga_logros.curso_id' => $curso_id]);
        $array_wheres = array_merge( $array_wheres, ['sga_logros.asignatura_id' => $asignatura_id] );
        $array_wheres = array_merge( $array_wheres, ['sga_logros.periodo_id' => $periodo_id] );
        
        $select_raw = 'CONCAT(sga_escala_valoracion.nombre_escala," (",sga_escala_valoracion.calificacion_minima,"-",sga_escala_valoracion.calificacion_maxima,")") AS campo4';

        return Logro::where($array_wheres)
                    ->leftJoin('sga_periodos','sga_periodos.id','=','sga_logros.periodo_id')
                    ->leftJoin('sga_cursos','sga_cursos.id','=','sga_logros.curso_id')
                    ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_logros.asignatura_id')
                    ->leftJoin('sga_escala_valoracion','sga_escala_valoracion.id','=','sga_logros.escala_valoracion_id')
                    ->select('sga_logros.*')
                    ->get();
    }



    public static function get_para_boletin( $periodo_id, $curso_id, $asignatura_id, $escala_valoracion_id )
    {
        return Logro::where(
                                [ 
                                    'periodo_id' => $periodo_id,
                                    'curso_id' => $curso_id,
                                    'asignatura_id' => $asignatura_id,
                                    'escala_valoracion_id' => $escala_valoracion_id,
                                ]
                            )
                        ->get();
    }

    // PADRE = CURSO, HIJO = asignaturas
    public static function get_registros_select_hijo($id_select_padre)
    {
        $registros = CursoTieneAsignatura::asignaturas_del_curso( $id_select_padre, null, null, null );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $campo) {
                            
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }
}
