<?php

namespace App\Http\Controllers\Calificaciones;

use Illuminate\Http\Request;
use App\Http\Requests;
//use Illuminate\Database\Eloquent\Model;

use App\Http\Controllers\Calificaciones\CalificacionController;

use App\Core\Colegio;
use App\Sistema\Aplicacion;

use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Periodo;


use App\Matriculas\Matricula;
use App\Calificaciones\Area;

use Input;
use DB;
use PDF;
use View;
use Auth;

class PensumController extends CalificacionController
{
	
    // muestra el formulario
    public function asignar_asignaturas()
    {
    	$periodos_lectivos = PeriodoLectivo::get_array_activos();
        $cursos = Curso::opciones_campo_select();

        $miga_pan = [
                        ['url'=>'calificaciones?id='.Input::get('id'),'etiqueta'=>'Calificaciones'],
                        ['url'=>'NO','etiqueta'=>'Asignar asignaturas']
                    ];

        $tabla = '';
        $mensaje = '';

		return view('calificaciones.asignar_asignaturas', compact( 'periodos_lectivos', 'cursos', 'miga_pan','tabla','mensaje'));
    }

    /*
        La tabla de asignaturas asignadas
    */
    public function get_tabla_asignaturas_asignadas( $periodo_lectivo_id, $curso_id)
    {
        $registros_asignados = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo_id );

        $tabla = View::make( 'calificaciones.pensum.asignaturas_x_curso_tabla', compact( 'registros_asignados' ) )->render();

        // Asignaturas pendientes del curso
        $opciones = CursoTieneAsignatura::get_opciones_select_asignaturas_pendientes( $periodo_lectivo_id, $curso_id);

        return [ $opciones, $tabla ];        
    }
	
	/**
     *  GUARDAR Asignación de asignatura al curso.
     *
     */
    public function guardar_asignacion_asignatura( Request $request )
    {
        CursoTieneAsignatura::create( $request->all() );

        $registro = CursoTieneAsignatura::get_datos_asignacion( $request->periodo_lectivo_id, $request->curso_id, $request->asignatura_id );
        
        if ( $registro->maneja_calificacion ) 
        {
            $maneja_calificacion = 'Si';
        }else{
            $maneja_calificacion = 'No';
        }

        $fila = View::make( 'calificaciones.pensum.asignaturas_x_curso_tabla_fila', [
                                                        'orden_boletin' => $registro->orden_boletin,
                                                        'area_descripcion' => $registro->area,
                                                        'asignatura_descripcion' => $registro->descripcion,
                                                        'intensidad_horaria' => $registro->intensidad_horaria, 
                                                        'maneja_calificacion' => $maneja_calificacion,
                                                        'periodo_lectivo_id' => $registro->periodo_lectivo_id,
                                                        'curso_id' => $registro->curso_id,
                                                        'asignatura_id' => $registro->id,
                                                        'profesor' => 'No'
                                                    ] ) 
                        ->render();

        return [$fila, $registro->intensidad_horaria];
    }


    // Proceso de eliminar una asignación
    public static function eliminar_asignacion_asignatura( $periodo_lectivo_id, $curso_id, $asignatura_id)
    {
        
        $intensidad_horaria = CursoTieneAsignatura::intensidad_horaria_asignatura_curso( $periodo_lectivo_id, $curso_id, $asignatura_id );

        CursoTieneAsignatura::eliminar_asignacion( $periodo_lectivo_id, $curso_id, $asignatura_id );

        return $intensidad_horaria;
        
    }


    public function cambiar_orden_asignatura( $periodo_lectivo_id, $curso_id, $asignatura_id, $nueva_posicion )
    {
        
        CursoTieneAsignatura::where( [
                                    'periodo_lectivo_id' => $periodo_lectivo_id,
                                    'curso_id' => $curso_id,
                                    'asignatura_id' => $asignatura_id 
                                    ] )
                            ->update( [ 'orden_boletin' => $nueva_posicion ] );

        return 'true';
        
    }

    /**
     * Revisar las Todas Asignaturas por curso con su intensidad horaria
     *
     */
    public function revisar_asignaciones()
    {
        if ( !is_null( Input::get('periodo_lectivo_id') ) )
        {
            $periodo_lectivo = PeriodoLectivo::find ( Input::get('periodo_lectivo_id') );
        }else{
            $periodo_lectivo = PeriodoLectivo::get_actual();
        }


        $periodos_lectivos = PeriodoLectivo::get_array_activos();
        
        $todas_las_asignaturas = Asignatura::get_registros_estado_activo();

        $todos_los_cursos = Curso::get_registros_estado_activo();

        $todas_las_asignaciones = CursoTieneAsignatura::asignaturas_del_curso( null, null, $periodo_lectivo->id, null );

        $tabla = View::make( 'calificaciones.pensum.todas_asignaciones_tabla', compact( 'periodos_lectivos', 'periodo_lectivo', 'todas_las_asignaturas', 'todos_los_cursos', 'todas_las_asignaciones' ) )->render();

        $miga_pan = [
                        ['url'=>'calificaciones?id='.Input::get('id'),'etiqueta'=>'Calificaciones'],
                        ['url'=>'NO','etiqueta'=>'Revisar asignaturas asignadas']
                    ];

        return view('calificaciones.revisar_asignaciones_asignaturas',compact('tabla','miga_pan'));
    }


    /**
     * Formulario para Copiar todas las asignaciones de un periodo lectivo a otro
     *
     */
    public function copiar_asignaciones()
    {
        $periodos_lectivos = PeriodoLectivo::get_array_activos();

        $miga_pan = [
                        ['url'=>'calificaciones?id='.Input::get('id'),'etiqueta'=>'Calificaciones'],
                        ['url'=>'NO','etiqueta'=>'Copiar asignaciones']
                    ];

        return view('calificaciones.pensum.copiar_asignaciones_asignaturas',compact( 'periodos_lectivos', 'miga_pan' ) );
    }

    public function periodo_lectivo_tiene_asignaciones( $periodo_lectivo_id )
    {
        $asignaciones = CursoTieneAsignatura::asignaturas_del_curso( null, null, $periodo_lectivo_id, null );

        if ( !empty( $asignaciones->toArray() ) )
        {
            return 1; // Ya hay asignaciones para el periodo lectivo
        }

        return 0;
    }

    public function copiar_asignaciones_procesar( $periodo_lectivo_origen_id, $periodo_lectivo_destino_id )
    {
        $asignaciones = CursoTieneAsignatura::asignaturas_del_curso( null, null, $periodo_lectivo_origen_id, null );

        foreach( $asignaciones as $fila )
        {
            $fila->periodo_lectivo_id = $periodo_lectivo_destino_id;

            CursoTieneAsignatura::create( $fila->toArray() );
        }

        return 1;
    }
}