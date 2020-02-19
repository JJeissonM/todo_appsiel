<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
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
use App\Calificaciones\Periodo;
use App\Calificaciones\Logro;
use App\Calificaciones\EncabezadoCalificacion;


use App\Cuestionarios\Pregunta;
use App\Cuestionarios\Cuestionario;
use App\Cuestionarios\ActividadEscolar;

use App\Tesoreria\TesoLibretasPago;
use App\Tesoreria\TesoCarteraEstudiante;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
            $this->estudiante = Estudiante::where( 'user_id', Auth::user()->id)->get()->first();
        }
    }
    
    public function index()
    {
    	if( !is_null($this->estudiante) )
        {
    		$estudiante = $this->estudiante;
        	$miga_pan = [
                    ['url'=>'NO','etiqueta'=>'Académico estudiante']
                ];
            return view('academico_estudiante.index',compact( 'miga_pan', 'estudiante') );
        }else{
            return redirect('inicio')->with('mensaje_error', 'El usuario actual no tiene perfil de estudiante.');
        }
    }
    
    public function horario()
    {
    	$miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Horario']
            ];

    	return view('academico_estudiante.horario',compact('miga_pan'));
    }
    
    public function calificaciones()
    {
        $estudiante = $this->estudiante;

        $opciones = Periodo::where('id_colegio','=', $this->colegio->id)
            ->where('estado','=','Activo')
            ->where('cerrado','=',0)
            ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $anio = explode("-",$opcion->fecha_desde)[0];
            $vec[$opcion->id]=$anio.' > '.$opcion->descripcion;
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

        return View::make('calificaciones.incluir.notas_estudiante_periodo_tabla', compact('registros','periodo_id','curso_id'))->render();
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
    
    public function actividades_escolares()
    {
        $actividades = DB::table('estudiante_tiene_actividades_escolares')
                        ->leftJoin('actividades_escolares','actividades_escolares.id','=','estudiante_tiene_actividades_escolares.actividad_escolar_id')
                        ->where('estudiante_id',$this->estudiante->id)
                        ->where('actividades_escolares.estado','Activo')
                        ->get();

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'NO','etiqueta'=>'Actividades escolares']
            ];

        return view('calificaciones.actividades_escolares.index_estudiantes',compact('actividades','miga_pan'));
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
}
