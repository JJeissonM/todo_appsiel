<?php

namespace App\Http\Controllers\AcademicoDocente;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;
use App\User;

use App\Matriculas\Curso;
use App\Calificaciones\Periodo;

use App\Cuestionarios\ActividadEscolar;

use App\AcademicoDocente\PlanClaseEstrucPlantilla;
use App\AcademicoDocente\PlanClaseEncabezado;
use App\AcademicoDocente\PlanClaseRegistro;

use App\AcademicoDocente\AsignacionProfesor;
use App\Calificaciones\Logro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

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

        $carga_academica_profesor = AsignacionProfesor::get_asignaturas_x_curso( $request->user_id, $request->periodo_lectivo_id );

        $plantilla = PlanClaseEstrucPlantilla::get_actual( $request->periodo_lectivo_id );
        $primer_elemento_plantilla = $plantilla->elementos()->get()->first();
        $etiqueta_primer_elemento_plantilla = '';
        $id_primer_elemento_plantilla = 0;
        if ( !is_null($primer_elemento_plantilla) )
        {
            $etiqueta_primer_elemento_plantilla = $primer_elemento_plantilla->descripcion;
            $id_primer_elemento_plantilla = $primer_elemento_plantilla->id;
        }

        $curso = '';
        
        $lineas_planes_clases = [];

        foreach ($carga_academica_profesor as $asignacion)
        {
            $curso = Curso::find($asignacion->curso_id);

            $linea = (object)[ 'curso' => $curso->descripcion, 'asignatura' => $asignacion->Asignatura, 'lista_planes_clases' => '' ];

            $planes_profesor_asignatura = PlanClaseEncabezado::where( 'plantilla_plan_clases_id', $plantilla->id )
                                                ->whereBetween( 'fecha', [ $request->fecha_desde, $request->fecha_hasta ] )
                                                ->where( 'curso_id', $asignacion->curso_id )
                                                ->where( 'asignatura_id', $asignacion->id_asignatura )
                                                ->where( 'user_id', $request->user_id )
                                                ->get();

            $a = 0;
            $lista_planes_asignatura = [];
            foreach ($planes_profesor_asignatura as $plan)
            {

                $lista_planes_asignatura[$a]['contenido_primer_elemento_plantilla'] = PlanClaseRegistro::where( 'plan_clase_encabezado_id', $plan->id )
                                                        ->where( 'plan_clase_estruc_elemento_id', $id_primer_elemento_plantilla )
                                                        ->value('contenido');

                $lista_planes_asignatura[$a]['fecha_plan_clases'] = $plan->fecha;

                $lista_planes_asignatura[$a]['enlace_plan_clases'] = '<a href="' . url('sga_planes_clases/' . $plan->id . '?id=5&id_modelo=184&id_transaccion=') . '" target="_blank"> Revisar plan de clases </a>';
                $a++;
            }
            
            $linea->lista_planes_clases = $lista_planes_asignatura;
            
            $lineas_planes_clases[] = $linea;
        }
        
        $profesor = User::find( $request->user_id );

        $vista = View::make('academico_docente.reportes.resumen_planes', compact( 'plantilla', 'etiqueta_primer_elemento_plantilla', 'lineas_planes_clases', 'curso', 'profesor', 'fecha_desde', 'fecha_hasta') )->render();

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

    public function logros_ingresados_x_periodo( Request $request )
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') ) 
        {
            return '<h2>Su perfil de usuario no tiene permiso para generar reportes.</h2>';
        }

        $listado_asignaciones = AsignacionProfesor::get_asignaturas_x_curso( $request->user_id, $request->periodo_lectivo_id );

        $logros_del_periodo = Logro::where( [
                                                ['periodo_id', '=', $request->periodo_id]
                                            ] )
                                        ->get();

        
        $curso = (object)['descripcion' => '' ];
        $lineas_asignaturas = [];

        foreach ($listado_asignaciones as $asignacion)
        {

            $curso = Curso::find($asignacion->curso_id);

            if ( is_null($curso) )
            {
                $curso = (object)['descripcion' => '' ];
            }

            $linea = (object)[ 'curso' => $curso->descripcion, 'asignatura' => $asignacion->Asignatura, 'lista_logros' => '---'];

            $a = 0;
            $lista_logros_del_periodo = [];
            foreach ($logros_del_periodo as $logro)
            {

                if ( $logro->asignatura_id == $asignacion->id_asignatura && $logro->curso_id == $asignacion->curso_id )
                {
                    $lista_logros_del_periodo[$a]['descripcion'] = $logro->escala_valoracion->nombre_escala . ': ' . substr($logro->descripcion,0,150) . '...';

                    $lista_logros_del_periodo[$a]['enlace_logro'] = '<a href="' . url('web/' . $logro->id . '?id=2&id_modelo=70&id_transaccion=') . '" target="_blank"> Revisar Logro </a>';
                    $a++;
                }

            }
            
            $linea->lista_logros = $lista_logros_del_periodo;

            $lineas_asignaturas[] = $linea;            
        }
        
        $profesor = User::find( $request->user_id );
        $periodo = Periodo::find( $request->periodo_id );

        $vista = View::make('academico_docente.reportes.logros_ingresados_x_periodo', compact( 'lineas_asignaturas', 'curso', 'profesor', 'periodo') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

}