<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use DB;
use View;
use Lava;
use Input;
use Carbon\Carbon;

use App\Http\Controllers\Matriculas\ObservadorEstudianteController;

use App\Core\Colegio;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\Periodo;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CalificacionAuxiliar;
use App\Calificaciones\Calificacion;
use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\Logro;

use App\Calificaciones\CursoTieneAsignatura;

use App\Calificaciones\ObservacionesBoletin;

use App\AcademicoDocente\PlanClaseEncabezado;
use App\AcademicoDocente\PlanClaseRegistro;

use App\AcademicoEstudiante\SgaEstudianteReconocimiento;

use App\Cuestionarios\ActividadEscolar;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoPlanPagosEstudiante;

use App\AcademicoEstudiante\ProgramacionAulaVirtual;


class AcademicoEstudianteController extends Controller
{

    protected $colegio;
    protected $estudiante;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        if (Auth::check()) {
            $this->colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();
            $this->estudiante = Estudiante::where('user_id', Auth::user()->id)->get()->first();
        }
    }

    public function index()
    {
        if (is_null($this->estudiante)) {
            return redirect('inicio')->with('mensaje_error', 'El usuario actual no tiene perfil de estudiante.');
        }

        $matricula = Matricula::get_matricula_activa_un_estudiante($this->estudiante->id);

        if (is_null($matricula)) {
            return redirect('inicio')->with('mensaje_error', 'El estudiante no tiene alguna matrícua activa.');
        }

        $curso = Curso::find($matricula->curso_id);

        $estudiante = $this->estudiante;

        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Académico estudiante']
        ];
        return view('academico_estudiante.index', compact('miga_pan', 'estudiante', 'curso'));
    }



    public function horario()
    {
        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Horario']
        ];

        $matricula = Matricula::get_matricula_activa_un_estudiante($this->estudiante->id);
        $curso = Curso::find($matricula->curso_id);

        return view('academico_estudiante.horario', compact('miga_pan', 'curso'));
    }


    public function mis_asignaturas($curso_id)
    {

        $curso = Curso::find($curso_id);

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Mis Asignaturas: ' . $curso->descripcion]
        ];

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($curso_id, null, null, null);

        $periodo_lectivo = PeriodoLectivo::get_actual();

        return view('academico_estudiante.mis_asignaturas', compact('miga_pan', 'curso_id', 'asignaturas', 'periodo_lectivo'));
    }



    public function calificaciones()
    {
        $estudiante = $this->estudiante;

        $libreta_pago = $estudiante->matricula_activa()->libretas_pagos->where('estado', 'Activo')->first();

        if (!is_null($libreta_pago)) {
            $cantidad_facturas_vencidas = $libreta_pago->lineas_registros_plan_pagos->where('estado', 'Vencida')->count();

            if ($cantidad_facturas_vencidas > config('matriculas.cantidad_facturas_vencidas_permitidas')) {
                return redirect('academico_estudiante/mi_plan_de_pagos/' . $libreta_pago->id . '?id=6')->with('mensaje_error', 'El estudiante tiene más de ' . config('matriculas.cantidad_facturas_vencidas_permitidas') . ' facturas vencidas. Debe ponerse al día para consultar Calificaciones y Boletines.');
            }
        }

        $opciones = Periodo::get_activos_periodo_lectivo();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->periodo_lectivo_descripcion . ' > ' . $opcion->descripcion;
        }

        $periodos = $vec;

        $matricula = Matricula::where('estado', 'Activo')->where('id_estudiante', $estudiante->id)->get()[0];

        $curso = Curso::find($matricula->curso_id);

        $codigo_matricula = $matricula->codigo;

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Calificaciones']
        ];

        return view('academico_estudiante.calificaciones', compact('miga_pan', 'periodos', 'estudiante', 'curso', 'codigo_matricula'));
    }

    public function ajax_calificaciones(Request $request)
    {
        $periodo = Periodo::find($request->periodo_id);
        $curso = Curso::find($request->curso_id);

        if ($periodo->periodo_de_promedios) {
            $periodos_del_anio_lectivo = Periodo::where('periodo_lectivo_id', $periodo->periodo_lectivo_id)
                ->where('estado', 'Activo')
                ->orderBy('periodo_de_promedios')
                ->get();

            $registros = $this->get_registros_tabla_datos($this->estudiante, $periodos_del_anio_lectivo, $periodo, $curso);
        } else {
            $registros = CalificacionAuxiliar::get_todas_un_estudiante_periodo($this->estudiante->id, $request->periodo_id);
        }

        $periodo_id = $request->periodo_id;
        $curso_id = $request->curso_id;

        $observacion_boletin = ObservacionesBoletin::get_x_estudiante($periodo_id, $curso_id, $this->estudiante->id);
 
        if ($observacion_boletin == null) {
            $observacion_boletin = (object)['puesto' => '', 'observacion' => ''];
        }

        $estudiante = Estudiante::get_datos_basicos($this->estudiante->id);
        
        if ($periodo->periodo_de_promedios) {
            return View::make('calificaciones.incluir.notas_estudiante_periodo_final', compact('registros', 'periodo', 'curso', 'observacion_boletin', 'estudiante', 'periodos_del_anio_lectivo'))->render();
        } else {
            return View::make('calificaciones.incluir.notas_estudiante_periodo_tabla', compact('registros', 'periodo_id', 'curso_id', 'observacion_boletin', 'estudiante'))->render();
        }
    }

    public function get_registros_tabla_datos($estudiante, $periodos_del_anio_lectivo, $periodo, $curso)
    {
        $filas = [];
        $asignaturas_asignadas = $curso->asignaturas_asignadas->where('periodo_lectivo_id', $periodo->periodo_lectivo_id);
        foreach ($asignaturas_asignadas as $registro_curso_tiene_asignatura) {
            $obj_fila = (object)['asignatura' => '', 'periodos' => '', 'escala_valoracion_periodo_final' => '', 'logros' => ''];

            $asignatura = $registro_curso_tiene_asignatura->asignatura;
            $obj_fila->asignatura = $asignatura;
            $obj_fila->escala_valoracion_periodo_final = '';

            $periodos = [];
            foreach ($periodos_del_anio_lectivo as $periodo) {
                $obj_periodos = (object)['periodo' => '', 'calificacion' => ''];
                $obj_periodos->periodo = $periodo;
                $obj_calificacion = Calificacion::get_para_boletin($periodo->id, $curso->id, $estudiante->id, $asignatura->id);
                $calificacion = 0;
                if (!is_null($obj_calificacion)) {
                    $calificacion = $obj_calificacion->calificacion;
                }

                $obj_periodos->calificacion = $calificacion;
                $periodos[] = $obj_periodos;

                if ($periodo->periodo_de_promedios) {
                    $escala_valoracion = EscalaValoracion::get_escala_segun_calificacion($calificacion, $periodo->periodo_lectivo_id);

                    $obj_fila->escala_valoracion_periodo_final = $escala_valoracion->nombre_escala;

                    $obj_fila->logros = Logro::get_para_boletin($periodo->id, $curso->id, $asignatura->id, $escala_valoracion->id);
                }
            }

            $obj_fila->periodos = $periodos;

            $filas[] = $obj_fila;
        }

        return $filas;
    }



    public function observador_show($estudiante_id)
    {
        $view_pdf = ObservadorEstudianteController::vista_preliminar($estudiante_id, 'show');

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Observador']
        ];

        return view('academico_estudiante.observador_show', compact('miga_pan', 'view_pdf', 'estudiante_id'));
    }



    public function agenda()
    {
        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Agenda']
        ];

        return view('academico_estudiante.agenda', compact('miga_pan'));
    }


    public function actividades_escolares($curso_id, $asignatura_id)
    {
        $actividades = ActividadEscolar::get_actividades_periodo_lectivo_actual($curso_id, $asignatura_id);

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'mis_asignaturas/' . $curso_id . '?id=' . Input::get('id'), 'etiqueta' => 'Mis asignaturas: ' . $curso->descripcion],
            ['url' => 'NO', 'etiqueta' => 'Actividades escolares: ' . $asignatura->descripcion]
        ];

        return view('calificaciones.actividades_escolares.index_estudiantes', compact('actividades', 'miga_pan'));
    }


    public function guias_planes_clases($curso_id, $asignatura_id)
    {
        $planes = PlanClaseEncabezado::consultar_guias_estudiantes( $curso_id, $asignatura_id );

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
                        ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
                        ['url' => 'mis_asignaturas/' . $curso_id . '?id=' . Input::get('id'), 'etiqueta' => 'Mis asignaturas: ' . $curso->descripcion],
                        ['url' => 'NO', 'etiqueta' => 'Guías planes de clases: ' . $asignatura->descripcion]
                    ];

        return view('calificaciones.actividades_escolares.guias_planes_clases', compact('planes', 'asignatura', 'curso', 'miga_pan'));
    }



    public function ver_guia_plan_clases($curso_id, $asignatura_id, $plan_id)
    {

        $encabezado = PlanClaseEncabezado::get_registro_impresion($plan_id);

        $registros = PlanClaseRegistro::get_registros_impresion_guia($plan_id);

        $vista = View::make('academico_docente.planes_clases.vista_impresion', compact('encabezado', 'registros'))->render();

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'mis_asignaturas/' . $curso_id . '?id=' . Input::get('id'), 'etiqueta' => 'Mis asignaturas: ' . $curso->descripcion],
            ['url' => 'academico_estudiante/guias_planes_clases/' . $curso_id . '/' . $asignatura_id . '?id=' . Input::get('id'), 'etiqueta' => 'Guías planes de clases: ' . $asignatura->descripcion],
            ['url' => 'NO', 'etiqueta' => 'Guías planes de clases: ' . $asignatura->descripcion]
        ];

        return view('academico_estudiante.guia_plan_clases_show', compact('miga_pan', 'vista', 'plan_id'));
    }



    public function mi_plan_de_pagos($id_libreta)
    {

        $libreta = TesoLibretasPago::find($id_libreta);
        if ($libreta != null) {

            $estudiante = $this->estudiante;

            $cartera = TesoPlanPagosEstudiante::where('id_libreta', $id_libreta)->get();

            $matricula = Matricula::where('estado', 'Activo')->where('id_estudiante', $estudiante->id)->get()->first();

            $curso = Curso::find($matricula->curso_id);

            $codigo_matricula = $matricula->codigo;

            $miga_pan = [
                ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
                ['url' => 'NO', 'etiqueta' => 'Libreta de pagos']
            ];

            return view('academico_estudiante.mi_plan_de_pagos', compact('libreta', 'estudiante', 'cartera', 'miga_pan', 'codigo_matricula', 'curso'));
        } else {
            return redirect('academico_estudiante' . '?id=' . Input::get('id'))->with('mensaje_error', 'La libreta de pagos no existe');
        }
    }

    public function consultar_preinforme($periodo_id, $curso_id, $estudiante_id)
    {

        $periodo = Periodo::find($periodo_id);
        $anio = PeriodoLectivo::find($periodo->periodo_lectivo_id)->descripcion;

        $estudiante = Estudiante::get_datos_basicos($estudiante_id);

        $curso = Curso::find($curso_id);

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($curso_id, null, null, null);

        return view('academico_estudiante.preinforme_academico', compact('estudiante', 'periodo', 'anio', 'curso', 'asignaturas'));
    }

    public function reconocimientos()
    {
        $reconocimientos = SgaEstudianteReconocimiento::where('estudiante_id', $this->estudiante->id)
            ->where('estado', 'Activo')
            ->get();

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Reconocimientos']
        ];

        return view('academico_estudiante.reconocimientos', compact('miga_pan', 'reconocimientos'));
    }

    public function aula_virtual( $curso_id )
    {
        $dia_semana = $this->get_dia_semana( Input::get('fecha') );
        $eventos = ProgramacionAulaVirtual::where([
                                                                    [ 'dia_semana', '=', $dia_semana ]
                                                                ])
                                                        ->orWhere('fecha', Input::get('fecha'))
                                                        ->orderBy('hora_inicio')
                                                        ->get();

        //dd( $eventos );

        $miga_pan = [
            ['url' => 'academico_estudiante?id=' . Input::get('id'), 'etiqueta' => 'Académico estudiante'],
            ['url' => 'NO', 'etiqueta' => 'Aula virtual']
        ];

        $curso = Curso::find($curso_id);


        return view( 'academico_estudiante.aula_virtual', compact( 'eventos', 'dia_semana', 'curso', 'miga_pan' ) );
    }

    public function get_dia_semana( $fecha )
    {
        $fecha2 = Carbon::createFromFormat('Y-m-d', $fecha );

        $weekMap = [
                        0 => 'domingo',
                        1 => 'lunes',
                        2 => 'martes',
                        3 => 'miercoles',
                        4 => 'jueves',
                        5 => 'viernes',
                        6 => 'sabado',
                    ];

        $dayOfTheWeek = $fecha2->dayOfWeek;
        return $weekMap[ $dayOfTheWeek ];
    }
}
