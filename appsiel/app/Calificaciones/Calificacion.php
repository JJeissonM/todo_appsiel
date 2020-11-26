<?php

namespace App\Calificaciones;

use App\Boletin;
use Illuminate\Database\Eloquent\Model;

use DB;

use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Periodo;

class Calificacion extends Model
{
    protected $table = 'sga_calificaciones';

    // logros es un string donde se almacenan códigos de logros separados por coma (usado para logros adicionales)
	protected $fillable = [ 'codigo_matricula', 'id_colegio', 'anio', 'id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'calificacion', 'logros', 'creado_por', 'modificado_por'];

    /**
     * Obtener todas las calificaciones con sus datos relacionados
     */
    public static function get_calificaciones( $id_colegio, $curso_id, $asignatura_id, $periodo_id = null )
    {
        $array_wheres = ['sga_calificaciones.id_colegio' => $id_colegio];

        if ( $curso_id != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.curso_id' => $curso_id]);
        }

        if ( $asignatura_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_calificaciones.id_asignatura' => $asignatura_id] );
        }

        if ( $periodo_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_calificaciones.id_periodo' => $periodo_id] );
        }

        return Calificacion::where($array_wheres)
                        ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones.id_periodo')
                        ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
                        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
                        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                        ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones.id_asignatura')
                        ->select(
                                'sga_calificaciones.anio AS campo1',
                                'sga_periodos.descripcion AS campo2',
                                'sga_cursos.descripcion AS campo3',
                                DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo4' ),
                                'sga_asignaturas.descripcion AS campo5',
                                'sga_calificaciones.calificacion AS campo6',
                                'sga_calificaciones.id AS campo8')
                        ->get()
                        ->toArray();
    }

    /**
     * Obtener las calificaciones con sus datos relacionados
     */
    public static function get_calificaciones_boletines( $id_colegio, $curso_id, $asignatura_id, $periodo_id )
    {
        $select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS nombre_completo_estudiante';

        $array_wheres = ['sga_calificaciones.id_colegio' => $id_colegio];

        if ( $curso_id != null ) {
            $array_wheres = array_merge($array_wheres, ['sga_calificaciones.curso_id' => $curso_id]);
        }

        if ( $asignatura_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_calificaciones.id_asignatura' => $asignatura_id] );
        }

        if ( $periodo_id != null ) {
            $array_wheres = array_merge( $array_wheres, ['sga_calificaciones.id_periodo' => $periodo_id] );
        }

        return Calificacion::where($array_wheres)
                        ->leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_calificaciones.id_periodo')
                        ->leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_calificaciones.curso_id')
                        ->leftJoin('sga_estudiantes', 'sga_estudiantes.id', '=', 'sga_calificaciones.id_estudiante')
                        ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones.id_asignatura')
                        ->select(
                                    'sga_calificaciones.anio',
                                    'sga_periodos.id AS periodo_id',
                                    'sga_cursos.id AS curso_id',
                                    'sga_estudiantes.id AS estudiante_id',
                                    'sga_asignaturas.id AS asignatura_id',
                                    'sga_calificaciones.calificacion',
                                    'sga_calificaciones.id AS calificacion_id')
                        ->get();

    }



    public static function get_para_boletin( $periodo_id, $curso_id, $estudiante_id, $asignatura_id )
    {
        return Calificacion::where(
                                    [ 
                                        'id_periodo' => $periodo_id,
                                        'curso_id' => $curso_id,
                                        'id_estudiante' => $estudiante_id,
                                        'id_asignatura' => $asignatura_id
                                    ]
                                )
                            ->get()
                            ->first();
    }


    public static function get_la_calificacion($periodo_id, $curso_id, $estudiante_id, $asignatura_id)
    {
        $periodo = Periodo::find( $periodo_id );

        $calificacion = Calificacion::where( [
                                                'id_periodo' => $periodo_id,
                                                'curso_id' => $curso_id,
                                                'id_estudiante' => $estudiante_id,
                                                'id_asignatura' => $asignatura_id 
                                            ] )
                                    ->get()
                                    ->first();
                    
        if ( !is_null($calificacion) ) 
        {
            $escala = EscalaValoracion::get_escala_segun_calificacion( $calificacion->calificacion, $periodo->periodo_lectivo_id );

            if ( !is_null($escala) ) 
            {
                $la_calificacion = (object)['valor' => $calificacion->calificacion, 
                                            'escala_id' => $escala->id, 
                                            'escala_descripcion' => $escala->nombre_escala, 
                                            'escala_abreviatura' => $escala->sigla, 
                                            'escala_nacional' => $escala->escala_nacional, 
                                            'logros' => $calificacion->logros
                                        ];
            }else{
                $la_calificacion = (object)['valor' => $calificacion->calificacion,
                                            'escala_id' => 0,
                                            'escala_descripcion' => '-',
                                            'escala_abreviatura' => '-',
                                            'escala_nacional' => '-', 
                                            'logros' => ''
                                        ];
            }

        }else{
            $la_calificacion = (object)['valor' => 0,
                                        'escala_id' => 0,
                                        'escala_descripcion' => '-',
                                        'escala_abreviatura' => '-',
                                        'escala_nacional' => '-', 
                                        'logros' => ''
                                    ];
        }

        return $la_calificacion;
    }


    public static function get_calificacion_promedio_asignatura_estudiante_periodos($periodos_promediar, $curso_id, $estudiante_id, $asignatura_id)
    {
        return Calificacion::whereIn('id_periodo',$periodos_promediar)
                            ->where( [ 
                                        'curso_id' => $curso_id,
                                        'id_estudiante' => $estudiante_id,
                                        'id_asignatura' => $asignatura_id 
                                    ] )
                            ->avg('calificacion');
    }


    public static function get_promedio_periodos($periodos_promediar, $curso_id, $estudiante_id, $asignatura_id)
    {
        $periodo = Periodo::find( $periodos_promediar[0] );

        $calificacion = number_format( Calificacion::whereIn('id_periodo',$periodos_promediar)
                                    ->where( [ 'curso_id' => $curso_id,
                                            'id_estudiante' => $estudiante_id,
                                            'id_asignatura' => $asignatura_id ] )->avg('calificacion'), 2, '.', ',' );
                    
        if ( !is_null($calificacion) ) 
        {
            $escala = EscalaValoracion::get_escala_segun_calificacion( $calificacion,$periodo->periodo_lectivo_id );

            if ( !is_null($escala) && !empty($escala) )
            {
                $la_calificacion = (object)['valor' => $calificacion, 
                'escala_id' => $escala->id, 
                'escala_descripcion' => $escala->nombre_escala, 
                'escala_abreviatura' => $escala->sigla, 
                'escala_nacional' => $escala->escala_nacional];
            }else{
                $la_calificacion = (object)['valor' => $calificacion, 'escala_id' => 0, 'escala_descripcion' => '
            -', 'escala_abreviatura' => '
            -', 'escala_nacional' => '
            -'];
            }

        }else{
            $la_calificacion = (object)['valor' => '-', 'escala_id' => 0, 'escala_descripcion' => '
            -', 'escala_abreviatura' => '
            -', 'escala_nacional' => '
            -'];
        }

        return $la_calificacion;
    }

    public static function get_cantidad_x_matricula( $colegio_id, $codigo_matricula)
    {
        return Calificacion::where(
                                    [ 
                                        'id_colegio' => $colegio_id,
                                        'codigo_matricula' => $codigo_matricula
                                    ]
                                )
                            ->count();
    }


    public static function calificaciones_promedio_por_estudiante( $periodo_id )
    {
        return Calificacion::leftJoin('sga_cursos','sga_cursos.id','=','sga_calificaciones.curso_id')
                            ->leftJoin('sga_niveles','sga_niveles.id','=','sga_cursos.nivel_grado')
                            ->leftJoin('sga_grados','sga_grados.id','=','sga_cursos.sga_grado_id')
                            ->leftJoin('sga_estudiantes','sga_estudiantes.id','=','sga_calificaciones.id_estudiante')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_estudiantes.core_tercero_id')
                            ->where('sga_calificaciones.id_periodo', $periodo_id)
                            ->select( 
                                        DB::raw('AVG(sga_calificaciones.calificacion) AS calificacion_prom'),
                                        'sga_cursos.descripcion AS Curso',
                                        'sga_niveles.descripcion AS Nivel',
                                        'sga_grados.descripcion AS Grado',
                                        DB::raw( 'CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo' ),
                                        'sga_estudiantes.imagen',
                                        'sga_calificaciones.id_estudiante' )
                            ->groupBy( 'sga_calificaciones.id_estudiante' )
                            ->get();
    }
}
