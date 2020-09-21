<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Core\Colegio;
use App\Matriculas\PeriodoLectivo;

/*
    Aquí se define el Pemsum académico (Asignaturas x curso con su intensidad horaria)
    Se debe crear por cada Periodo Lectivo
*/
class CursoTieneAsignatura extends Model
{
	protected $table = 'sga_curso_tiene_asignaturas';

    protected $fillable = ['periodo_lectivo_id','curso_id','asignatura_id','intensidad_horaria','orden_boletin','maneja_calificacion'];

    public static function asignaturas_del_curso( $curso_id, $area_id, $periodo_lectivo_id, $estado_asignaturas = null )
    {
        $empresa_id = 1;
        $user = Auth::user();
        if ( !is_null($user) )
        {
            $empresa_id = Auth::user()->empresa_id;
        }

        $colegio = Colegio::where('empresa_id', $empresa_id)->get()->first();

        $array_wheres = [
                            'sga_asignaturas.id_colegio' => $colegio->id
                        ];

        if ( $curso_id != null) 
        {
            $array_wheres = array_merge( $array_wheres, [ 'sga_curso_tiene_asignaturas.curso_id' => $curso_id ] );
        }



        if ( $area_id != null) 
        {
            $array_wheres = array_merge( $array_wheres, [ 'sga_asignaturas.area_id' => $area_id ] );
        }



        if ( $periodo_lectivo_id == null)
        {
            $array_wheres = array_merge( $array_wheres, [ 'sga_curso_tiene_asignaturas.periodo_lectivo_id' => PeriodoLectivo::get_actual()->id ] );
        }else{
            // Si la variable $periodo_lectivo_id tiene el valor 'todos', no se filtar por periodo 
            if ( $periodo_lectivo_id != 'todos' )
            {
                $array_wheres = array_merge( $array_wheres, [ 'sga_curso_tiene_asignaturas.periodo_lectivo_id' => $periodo_lectivo_id ] );
            }            
        }



        if ( $estado_asignaturas == null) 
        {
            $array_wheres = array_merge( $array_wheres, [ 'sga_asignaturas.estado' => 'Activo' ] );
        }else{
            $array_wheres = array_merge( $array_wheres, [ 'sga_asignaturas.estado' => $estado_asignaturas ] );
        }

        return CursoTieneAsignatura::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->leftJoin('sga_cursos','sga_cursos.id','=','sga_curso_tiene_asignaturas.curso_id')
                            ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_curso_tiene_asignaturas.asignatura_id')
                            ->leftJoin('sga_areas','sga_areas.id','=','sga_asignaturas.area_id')
                            ->where($array_wheres)
                            ->select(
                                        'sga_asignaturas.id',
                                        'sga_asignaturas.id AS asignatura_id',
                                        'sga_asignaturas.abreviatura',
                                        'sga_asignaturas.descripcion',
                                        'sga_curso_tiene_asignaturas.intensidad_horaria',
                                        'sga_curso_tiene_asignaturas.orden_boletin',
                                        'sga_curso_tiene_asignaturas.maneja_calificacion',
                                        'sga_areas.id as area_id',
                                        'sga_areas.descripcion as area',
                                        'sga_areas.orden_listados as orden',
                                        'sga_cursos.descripcion as curso_descripcion',
                                        'sga_curso_tiene_asignaturas.curso_id',
                                        'sga_periodos_lectivos.descripcion as periodo_lectivo_descripcion',
                                        'sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            //->orderBy('sga_areas.orden_listados','ASC')
                            ->orderBy('sga_curso_tiene_asignaturas.orden_boletin','ASC')
                            ->get();
    }

    public static function cursos_asignaturas_del_periodo_lectivo( $periodo_lectivo_id )
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();

        $array_wheres = [
                            'sga_asignaturas.id_colegio' => $colegio->id
                        ];

        $array_wheres = array_merge( $array_wheres, [ 'sga_curso_tiene_asignaturas.periodo_lectivo_id' => $periodo_lectivo_id ] );

        return CursoTieneAsignatura::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->leftJoin('sga_cursos','sga_cursos.id','=','sga_curso_tiene_asignaturas.curso_id')
                            ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_curso_tiene_asignaturas.asignatura_id')
                            ->leftJoin('sga_areas','sga_areas.id','=','sga_asignaturas.area_id')
                            ->where($array_wheres)
                            ->select(
                                        'sga_asignaturas.id',
                                        'sga_asignaturas.id AS asignatura_id',
                                        'sga_asignaturas.abreviatura',
                                        'sga_asignaturas.descripcion',
                                        'sga_curso_tiene_asignaturas.intensidad_horaria',
                                        'sga_curso_tiene_asignaturas.orden_boletin',
                                        'sga_curso_tiene_asignaturas.maneja_calificacion',
                                        'sga_areas.id as area_id',
                                        'sga_areas.descripcion as area',
                                        'sga_areas.orden_listados as orden',
                                        'sga_cursos.descripcion as curso_descripcion',
                                        'sga_curso_tiene_asignaturas.curso_id',
                                        'sga_periodos_lectivos.descripcion as periodo_lectivo_descripcion',
                                        'sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->orderBy('sga_areas.orden_listados','ASC')
                            ->orderBy('sga_curso_tiene_asignaturas.orden_boletin','ASC')
                            ->get();
    }


    public static function get_datos_asignacion( $periodo_lectivo_id, $curso_id, $asignatura_id )
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()->first();

        $array_wheres = [
                            'sga_asignaturas.id_colegio' => $colegio->id,
                            'sga_curso_tiene_asignaturas.periodo_lectivo_id' => $periodo_lectivo_id,
                            'sga_curso_tiene_asignaturas.curso_id' => $curso_id,
                            'sga_curso_tiene_asignaturas.asignatura_id' => $asignatura_id
                        ];

        return CursoTieneAsignatura::leftJoin('sga_periodos_lectivos','sga_periodos_lectivos.id','=','sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->leftJoin('sga_cursos','sga_cursos.id','=','sga_curso_tiene_asignaturas.curso_id')
                            ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_curso_tiene_asignaturas.asignatura_id')
                            ->leftJoin('sga_areas','sga_areas.id','=','sga_asignaturas.area_id')
                            ->where($array_wheres)
                            ->select(
                                        'sga_asignaturas.id',
                                        'sga_asignaturas.abreviatura',
                                        'sga_asignaturas.descripcion',
                                        'sga_curso_tiene_asignaturas.intensidad_horaria',
                                        'sga_curso_tiene_asignaturas.orden_boletin',
                                        'sga_curso_tiene_asignaturas.maneja_calificacion',
                                        'sga_areas.id as area_id',
                                        'sga_areas.descripcion as area',
                                        'sga_areas.orden_listados as orden',
                                        'sga_cursos.descripcion as curso_descripcion',
                                        'sga_curso_tiene_asignaturas.curso_id',
                                        'sga_periodos_lectivos.descripcion as periodo_lectivo_descripcion',
                                        'sga_curso_tiene_asignaturas.periodo_lectivo_id')
                            ->get()
                            ->first();
    }

    public static function intensidad_horaria_asignatura_curso( $periodo_lectivo_id, $curso_id, $asignatura_id )
    {


        if ( $periodo_lectivo_id == null)
        {
            $periodo_lectivo_id = PeriodoLectivo::get_actual()->id;
        }
        
        return CursoTieneAsignatura::where( 'periodo_lectivo_id', $periodo_lectivo_id )
                                ->where( 'curso_id', $curso_id )
                                ->where( 'asignatura_id', $asignatura_id )
                                ->value( 'intensidad_horaria' );
    }

    public static function eliminar_asignacion( $periodo_lectivo_id, $curso_id, $asignatura_id )
    {
        CursoTieneAsignatura::where( 'periodo_lectivo_id', $periodo_lectivo_id )
                            ->where( 'curso_id', $curso_id )
                            ->where( 'asignatura_id', $asignatura_id )
                            ->delete();
    }


    public static function get_asignaturas_pendientes( $periodo_lectivo_id, $curso_id )
    {
        $asignaturas = Asignatura::where('estado', 'Activo')->get();

        /*  
            Esto se debería hacer en una sola consulta SQL y no usar el foreach()
        */

        foreach ($asignaturas as $key => $value )
        {
            $cantidad = CursoTieneAsignatura::where('periodo_lectivo_id', $periodo_lectivo_id)
                                            ->where('curso_id', $curso_id)
                                            ->where('asignatura_id', $value->id)
                                            ->count();
            if ( $cantidad > 0 )
            {
                $asignaturas->forget( $key );
            }
        }

        return $asignaturas;

    }

    public static function get_opciones_select_asignaturas_pendientes( $periodo_lectivo_id, $curso_id )
    {
        $opciones = CursoTieneAsignatura::get_asignaturas_pendientes( $periodo_lectivo_id, $curso_id );
        
        $select = '<option value="">Seleccionar... </option>';
        foreach ($opciones as $opcion){
            $select.='<option value="'.$opcion->id.'">'.$opcion->descripcion.'</option>';
        }

        return $select;
    }
}
