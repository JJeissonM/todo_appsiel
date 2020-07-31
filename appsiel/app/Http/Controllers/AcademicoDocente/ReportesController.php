<?php

namespace App\Http\Controllers\AcademicoDocente;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Sistema\Modelo;

use Input;
use View;
use Storage;
use Cache;
use Auth;

use App\User;

use App\Matriculas\Curso;
use App\Calificaciones\Periodo;

use App\Cuestionarios\ActividadEscolar;

use App\AcademicoDocente\PlanClaseEstrucPlantilla;
use App\AcademicoDocente\PlanClaseEncabezado;
use App\AcademicoDocente\PlanClaseRegistro;

use App\AcademicoDocente\AsignacionProfesor;


class ReportesController extends ModeloController
{

    public function resumen_planes_clases( Request $request )
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) 
        {
            return '<h2>Su perfil de usuario no tiene permiso para generar reportes.</h2>';
        }

        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;

        $listado_asignaciones = AsignacionProfesor::get_asignaturas_x_curso( $request->user_id, $request->periodo_lectivo_id );

        $plantilla = PlanClaseEstrucPlantilla::get_actual( $request->periodo_lectivo_id );

        $elementos_plantilla = $plantilla->elementos()->orderBy('orden')->get();

        $planes_profesor = PlanClaseEncabezado::where( 'plantilla_plan_clases_id', $plantilla->id )
                                            ->whereBetween( 'fecha', [ $request->fecha_desde, $request->fecha_hasta ] )
                                            ->where( 'user_id', $request->user_id )
                                            ->get();

        $curso = '';
        
        $lineas_asignaturas = [];

        // NOTA: SOLO SE VA A MOSTRAR UN PLAN POR ASIGNATURA
        foreach ($listado_asignaciones as $asignacion)
        {
            $curso = Curso::find($asignacion->curso_id);

            $linea = (object)[ 'curso' => $curso->descripcion, 'asignatura' => $asignacion->Asignatura, 'fecha' => '', 'contenido_elementos' => null];

            foreach ($planes_profesor as $plan)
            {

                if ( $plan->asignatura_id == $asignacion->id_asignatura && $plan->curso_id == $asignacion->curso_id )
                {
                    $linea->fecha = $plan->fecha;
                    $array_elementos = [];
                    foreach ($elementos_plantilla as $elemento)
                    {
                        $array_elementos[] =  PlanClaseRegistro::where( 'plan_clase_encabezado_id', $plan->id )
                                                        ->where( 'plan_clase_estruc_elemento_id', $elemento->id )
                                                        ->value('contenido');
                    }

                    $linea->contenido_elementos = $array_elementos;
                }
            }

            $lineas_asignaturas[] = $linea;
            
        }
        
        $profesor = User::find( $request->user_id );

        $vista = View::make('academico_docente.reportes.resumen_planes', compact( 'plantilla', 'elementos_plantilla', 'lineas_asignaturas', 'curso', 'profesor', 'fecha_desde', 'fecha_hasta') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function resumen_actividades_academicas( Request $request )
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) 
        {
            return '<h2>Su perfil de usuario no tiene permiso para generar reportes.</h2>';
        }

        $listado_asignaciones = AsignacionProfesor::get_asignaturas_x_curso( $request->user_id, $request->periodo_lectivo_id );

        $actividades_profesor = ActividadEscolar::where( 'created_by', $request->user_id )
                                                ->where( 'periodo_id', $request->periodo_id )
                                                ->get();

        
        $curso = (object)['descripcion' => '' ];
        $lineas_asignaturas = [];

        // NOTA: SOLO SE VA A MOSTRAR UN PLAN POR ASIGNATURA
        foreach ($listado_asignaciones as $asignacion)
        {

            $curso = Curso::find($asignacion->curso_id);

            if ( is_null($curso) )
            {
                $curso = (object)['descripcion' => '' ];
            }

            $linea = (object)[ 'curso' => $curso->descripcion, 'asignatura' => $asignacion->Asignatura, 'lista_actividades' => '---'];

            $a = 0;
            $lista_actividades_periodo = [];
            foreach ($actividades_profesor as $actividad)
            {

                if ( $actividad->asignatura_id == $asignacion->id_asignatura && $actividad->curso_id == $asignacion->curso_id )
                {
                    $lista_actividades_periodo[$a]['descripcion'] = $actividad->descripcion;

                    $lista_actividades_periodo[$a]['enlace_actividad'] = '<a href="' . url('actividades_escolares/ver_actividad/' . $actividad->id . '?id=5&id_modelo=38&id_transaccion=') . '" target="_blank"> Revisar Actividad </a>';
                    $a++;
                }


            }
            
            $linea->lista_actividades = $lista_actividades_periodo;

            $lineas_asignaturas[] = $linea;
            
        }


        // Actividades que no tienen una asignatura específica
        $linea = (object)[ 'curso' => $curso->descripcion, 'asignatura' => 'Todas las asignaturas', 'lista_actividades' => '---'];

        $actividades_profesor_sin_asignaturas = ActividadEscolar::where( 'created_by', $request->user_id )
                                                                ->where( 'periodo_id', $request->periodo_id )
                                                                ->where( 'asignatura_id', 0 )
                                                                ->get();
        $a = 0;
        $lista_actividades_periodo = [];
        foreach ($actividades_profesor_sin_asignaturas as $actividad)
        {

            $lista_actividades_periodo[$a]['descripcion'] = $actividad->descripcion;

            $lista_actividades_periodo[$a]['enlace_actividad'] = '<a href="' . url('actividades_escolares/ver_actividad/' . $actividad->id . '?id=5&id_modelo=38&id_transaccion=') . '" target="_blank"> Revisar Actividad </a>';
            $a++;
        }
        
        $linea->lista_actividades = $lista_actividades_periodo;

        $lineas_asignaturas[] = $linea;
        
        $profesor = User::find( $request->user_id );
        $periodo = Periodo::find( $request->periodo_id );

        $vista = View::make('academico_docente.reportes.resumen_actividades_academicas', compact( 'lineas_asignaturas', 'curso', 'profesor', 'periodo') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

}