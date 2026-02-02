<?php

namespace App\Http\Controllers\AcademicoDocente;
use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;
use App\User;

use App\Matriculas\Curso;
use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Periodo;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;

use App\Cuestionarios\ActividadEscolar;

use App\AcademicoDocente\PlanClaseEstrucPlantilla;
use App\AcademicoDocente\PlanClaseEncabezado;
use App\AcademicoDocente\PlanClaseRegistro;
use App\AcademicoDocente\GuiaAcademica;

use App\AcademicoDocente\AsignacionProfesor;
use App\Calificaciones\Logro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
                    $nombre_escala = 'ADICIONAL';
                    if ($logro->escala_valoracion != null) {
                        $nombre_escala = $logro->escala_valoracion->nombre_escala;
                    }
                    $lista_logros_del_periodo[$a]['descripcion'] = $nombre_escala . ': ' . substr($logro->descripcion,0,150) . '...';

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

    /**
     * Reporte de cumplimiento de guías académicas
     */
    public function cumplimiento_guias( Request $request )
    {
        $user = Auth::user();

        if ( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            return '<h2>Su perfil de usuario no tiene permiso para generar reportes.</h2>';
        }

        $periodoLectivoId = $request->periodo_lectivo_id ?: PeriodoLectivo::get_actual()->id;
        $periodoSeleccionado = $request->periodo_id ? Periodo::find($request->periodo_id) : null;

        $asignaturasQuery = CursoTieneAsignatura::leftJoin('sga_cursos', 'sga_cursos.id', '=', 'sga_curso_tiene_asignaturas.curso_id')
            ->leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_curso_tiene_asignaturas.asignatura_id')
            ->leftJoin('sga_asignaciones_profesores', function ($join) {
                $join->on('sga_asignaciones_profesores.curso_id', '=', 'sga_curso_tiene_asignaturas.curso_id')
                    ->on('sga_asignaciones_profesores.id_asignatura', '=', 'sga_curso_tiene_asignaturas.asignatura_id')
                    ->on('sga_asignaciones_profesores.periodo_lectivo_id', '=', 'sga_curso_tiene_asignaturas.periodo_lectivo_id');
            })
            ->leftJoin('users', 'users.id', '=', 'sga_asignaciones_profesores.id_user')
            ->where('sga_curso_tiene_asignaturas.periodo_lectivo_id', $periodoLectivoId)
            ->where('sga_asignaturas.estado', 'Activo');

        if ($request->curso_id) {
            $asignaturasQuery->where('sga_curso_tiene_asignaturas.curso_id', $request->curso_id);
        }

        if ($request->asignatura_id) {
            $asignaturasQuery->where('sga_curso_tiene_asignaturas.asignatura_id', $request->asignatura_id);
        }

        if ($request->user_id) {
            $asignaturasQuery->where('sga_asignaciones_profesores.id_user', $request->user_id);
        }

        $asignaturas = $asignaturasQuery
            ->groupBy(
                'sga_curso_tiene_asignaturas.curso_id',
                'sga_curso_tiene_asignaturas.asignatura_id',
                'sga_cursos.descripcion',
                'sga_asignaturas.descripcion',
                'sga_asignaciones_profesores.id_user',
                'users.name'
            )
            ->select(
                'sga_curso_tiene_asignaturas.curso_id',
                'sga_curso_tiene_asignaturas.asignatura_id',
                'sga_cursos.descripcion AS curso',
                'sga_asignaturas.descripcion AS asignatura',
                'sga_asignaciones_profesores.id_user AS user_id',
                'users.name AS profesor'
            )
            ->get();

        $guiasQuery = GuiaAcademica::leftJoin('sga_periodos', 'sga_periodos.id', '=', 'sga_plan_clases_encabezados.periodo_id')
            ->where('sga_plan_clases_encabezados.plantilla_plan_clases_id', GuiaAcademica::PLANTILLA_GUIA_ACADEMICA_ID)
            ->where('sga_periodos.periodo_lectivo_id', $periodoLectivoId);

        if ($request->periodo_id) {
            $guiasQuery->where('sga_plan_clases_encabezados.periodo_id', $request->periodo_id);
        }

        if ($request->curso_id) {
            $guiasQuery->where('sga_plan_clases_encabezados.curso_id', $request->curso_id);
        }

        if ($request->asignatura_id) {
            $guiasQuery->where('sga_plan_clases_encabezados.asignatura_id', $request->asignatura_id);
        }

        if ($request->user_id) {
            $guiasQuery->where('sga_plan_clases_encabezados.user_id', $request->user_id);
        }

        $registrosGuias = $guiasQuery
            ->groupBy(
                'sga_plan_clases_encabezados.curso_id',
                'sga_plan_clases_encabezados.asignatura_id',
                'sga_plan_clases_encabezados.user_id'
            )
            ->select(
                'sga_plan_clases_encabezados.curso_id',
                'sga_plan_clases_encabezados.asignatura_id',
                'sga_plan_clases_encabezados.user_id',
                DB::raw('COUNT(*) AS guias_elaboradas')
            )
            ->get()
            ->keyBy(function ($registro) {
                return implode('-', [
                    $registro->curso_id,
                    $registro->asignatura_id,
                    $registro->user_id ?: 0
                ]);
            });

        $periodoLabel = $periodoSeleccionado ? $periodoSeleccionado->descripcion : 'Todos los periodos';

        $lineas = $asignaturas->map(function ($registro) use ($registrosGuias, $periodoLabel) {
            $key = implode('-', [
                $registro->curso_id,
                $registro->asignatura_id,
                $registro->user_id ?: 0
            ]);
            $guiasElaboradas = $registrosGuias->has($key) ? $registrosGuias->get($key)->guias_elaboradas : 0;
            $requeridas = self::obtenerCantidadGuiasRequeridas($registro->curso_id, $registro->asignatura_id);
            $cumplimiento = $requeridas > 0
                ? round(($guiasElaboradas / $requeridas) * 100, 2)
                : ($guiasElaboradas ? 100 : 0);

            return (object)[
                'curso' => $registro->curso,
                'asignatura' => $registro->asignatura,
                'profesor' => $registro->profesor ?? 'Sin asignar',
                'periodo' => $periodoLabel,
                'guias_elaboradas' => $guiasElaboradas,
                'guias_requeridas' => $requeridas,
                'excedente' => max(0, $guiasElaboradas - $requeridas),
                'cumplimiento' => $cumplimiento,
            ];
        });

        $totales = [
            'elaboradas' => $lineas->sum('guias_elaboradas'),
            'requeridas' => $lineas->sum('guias_requeridas'),
        ];

        $periodo = $periodoSeleccionado;
        $curso = $request->curso_id ? Curso::find($request->curso_id) : null;
        $asignatura = $request->asignatura_id ? Asignatura::find($request->asignatura_id) : null;
        $profesor = $request->user_id ? User::find($request->user_id) : null;

        $vista = View::make('academico_docente.reportes.cumplimiento_guias', compact('lineas', 'periodo', 'curso', 'asignatura', 'profesor', 'totales'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    private static function obtenerCantidadGuiasRequeridas($curso_id, $asignatura_id)
    {
        $config = config('guias_academicas');
        $cantidadPorDefecto = (int) data_get($config, 'cantidad_por_defecto', 0);
        $cantidadPorCurso = data_get($config, 'cantidad_por_curso', []);
        $cantidadPorCursoAsignatura = data_get($config, 'cantidad_por_curso_asignatura', []);

        $cursoKey = (string) $curso_id;
        $asignaturaKey = (string) $asignatura_id;

        $valorEspecifico = data_get($cantidadPorCursoAsignatura, "$cursoKey.$asignaturaKey");
        if ($valorEspecifico !== null) {
            return (int) $valorEspecifico;
        }

        $valorCurso = data_get($cantidadPorCurso, $cursoKey);
        if ($valorCurso !== null) {
            return (int) $valorCurso;
        }

        return $cantidadPorDefecto;
    }

}
