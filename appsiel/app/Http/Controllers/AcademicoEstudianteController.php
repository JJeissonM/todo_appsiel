<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use DB;
use View;
use Lava;
use Input;

use App\Http\Controllers\Matriculas\ObservadorEstudianteController;

use App\Core\Colegio;
use App\Matriculas\Estudiante;
use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Periodo;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CalificacionAuxiliar;

use App\Calificaciones\CursoTieneAsignatura;

use App\Calificaciones\ObservacionesBoletin;

use App\AcademicoDocente\PlanClaseEncabezado;
use App\AcademicoDocente\PlanClaseRegistro;


use App\Cuestionarios\ActividadEscolar;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoCarteraEstudiante;


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

        if( Auth::check() ) 
        {
            $this->colegio = Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();
            $this->estudiante = Estudiante::where( 'user_id', Auth::user()->id )->get()->first();
        }
    }
    
    public function index()
    {
    	if( !is_null($this->estudiante) )
        {
            
            $matricula = Matricula::get_matricula_activa_un_estudiante( $this->estudiante->id );
            $curso = Curso::find( $matricula->curso_id );

    		$estudiante = $this->estudiante;

        	$miga_pan = [
                    ['url'=>'NO','etiqueta'=>'Académico estudiante']
                ];
            return view( 'academico_estudiante.index', compact( 'miga_pan', 'estudiante', 'curso') );
        }else{

            return redirect( 'inicio' )->with('mensaje_error', 'El usuario actual no tiene perfil de estudiante.');
        }
    }
    
    

    public function horario()
    {
        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Horario']
            ];

        $matricula = Matricula::get_matricula_activa_un_estudiante( $this->estudiante->id );
        $curso = Curso::find( $matricula->curso_id );

        return view('academico_estudiante.horario',compact('miga_pan', 'curso'));
    }


    public function mis_asignaturas( $curso_id )
    {

        $curso = Curso::find( $curso_id );

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Mis Asignaturas: '.$curso->descripcion]
            ];

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, null, null );

        $periodo_lectivo = PeriodoLectivo::get_actual();

        return view( 'academico_estudiante.mis_asignaturas', compact('miga_pan', 'curso_id', 'asignaturas','periodo_lectivo') );
    }


    
    public function calificaciones()
    {
        $estudiante = $this->estudiante;

        $opciones = Periodo::get_activos_periodo_lectivo();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->periodo_lectivo_descripcion . ' > ' . $opcion->descripcion;
        }

        $periodos = $vec;

        $matricula = Matricula::where('estado','Activo')->where('id_estudiante',$estudiante->id)->get()[0];

        $curso = Curso::find($matricula->curso_id);

        $codigo_matricula = $matricula->codigo;

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Calificaciones']
            ];

        return view('academico_estudiante.calificaciones',compact('miga_pan','periodos','estudiante','curso','codigo_matricula') );
    }
    
    public function ajax_calificaciones(Request $request)
    {
    	$select_raw = 'CONCAT(sga_estudiantes.apellido1," ",sga_estudiantes.apellido2," ",sga_estudiantes.nombres) AS campo4';
        
        $registros = CalificacionAuxiliar::get_todas_un_estudiante_periodo( $this->estudiante->id, $request->periodo_id );

        $periodo_id = $request->periodo_id;
        $curso_id = $request->curso_id;

        $observacion_boletin = ObservacionesBoletin::get_x_estudiante( $periodo_id, $curso_id, $this->estudiante->id );

        if( $observacion_boletin == null )
        {
            $observacion_boletin = (object)['puesto'=>'','observacion'=>''];
        }

        $estudiante = Estudiante::get_datos_basicos( $this->estudiante->id );

        return View::make( 'calificaciones.incluir.notas_estudiante_periodo_tabla', compact( 'registros', 'periodo_id', 'curso_id', 'observacion_boletin', 'estudiante') )->render();
    }


    
    public function observador_show($estudiante_id)
    {
    	$view_pdf = ObservadorEstudianteController::vista_preliminar($estudiante_id,'show');

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Observador']
            ];

    	return view('academico_estudiante.observador_show',compact('miga_pan','view_pdf','estudiante_id'));
    }
    


    public function agenda()
    {
    	$miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Agenda']
            ];

    	return view('academico_estudiante.agenda',compact('miga_pan'));
    }
    

    public function actividades_escolares( $curso_id, $asignatura_id )
    {
        $actividades = ActividadEscolar::get_actividades_periodo_lectivo_actual( $curso_id, $asignatura_id );

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'mis_asignaturas/'.$curso_id.'?id='.Input::get('id'),'etiqueta'=>'Mis asignaturas: '. $curso->descripcion ],
                ['url'=>'NO','etiqueta'=>'Actividades escolares: '. $asignatura->descripcion ]
            ];

        return view('calificaciones.actividades_escolares.index_estudiantes',compact('actividades','miga_pan'));
    }
    

    public function guias_planes_clases( $curso_id, $asignatura_id )
    {
        $planes = PlanClaseEncabezado::consultar_guias_estudiantes( $curso_id, $asignatura_id );

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'mis_asignaturas/'.$curso_id.'?id='.Input::get('id'),'etiqueta'=>'Mis asignaturas: '. $curso->descripcion ],
                ['url'=>'NO','etiqueta'=>'Guías planes de clases: '. $asignatura->descripcion]
            ];

        return view('calificaciones.actividades_escolares.guias_planes_clases',compact('planes', 'asignatura', 'curso', 'miga_pan'));
    }



    public function ver_guia_plan_clases( $curso_id, $asignatura_id, $plan_id)
    {

        $encabezado = PlanClaseEncabezado::get_registro_impresion( $plan_id );

        $registros = PlanClaseRegistro::get_registros_impresion_guia( $plan_id );

        $vista = View::make( 'academico_docente.planes_clases.vista_impresion', compact( 'encabezado', 'registros' ) )->render();

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'mis_asignaturas/'.$curso_id.'?id='.Input::get('id'),'etiqueta'=>'Mis asignaturas: '. $curso->descripcion ],
                ['url'=>'academico_estudiante/guias_planes_clases/'.$curso_id.'/'.$asignatura_id.'?id='.Input::get('id'),'etiqueta'=>'Guías planes de clases: '. $asignatura->descripcion],
                ['url'=>'NO','etiqueta'=>'Guías planes de clases: '. $asignatura->descripcion]
            ];

        return view( 'academico_estudiante.guia_plan_clases_show',compact( 'miga_pan', 'vista', 'plan_id') );

    }



    public function mi_plan_de_pagos($id_libreta)
    {
        
        $libreta = TesoLibretasPago::find($id_libreta);

        $estudiante = $this->estudiante;

        $cartera = TesoCarteraEstudiante::where('id_libreta',$id_libreta)->get();

        $matricula = Matricula::where('estado','Activo')->where('id_estudiante',$estudiante->id)->get()->first();

        $curso = Curso::find($matricula->curso_id);

        $codigo_matricula = $matricula->codigo;

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Libreta de pagos']
            ];

        return view('academico_estudiante.mi_plan_de_pagos',compact('libreta','estudiante','cartera','miga_pan','codigo_matricula','curso'));
    }

    public function consultar_preinforme( $periodo_id, $curso_id, $estudiante_id )
    {
        
        $periodo = Periodo::find( $periodo_id );
        $anio = PeriodoLectivo::find( $periodo->periodo_lectivo_id )->descripcion;

        $estudiante = Estudiante::get_datos_basicos( $estudiante_id );

        $curso = Curso::find( $curso_id );

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, null, null );

        return view('academico_estudiante.preinforme_academico',compact( 'estudiante','periodo','anio','curso','asignaturas'));
    }


    
}
