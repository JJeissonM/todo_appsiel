<?php

namespace App\Http\Controllers\AcademicoDocente;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Matriculas\ObservadorEstudianteController;


use Auth;
use DB;
use Hash;
use Mail;
use View;
use Input;
use App\User;

use App\AcademicoDocente\AsignacionProfesor;

use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\Asignatura;
use App\Calificaciones\Calificacion;
use App\Calificaciones\Periodo;
use App\Calificaciones\Logro;
use App\AcademicoDocente\Asignacion;

use App\Matriculas\CatalogoAspecto;
use App\Matriculas\TiposAspecto;
use App\Matriculas\AspectosObservador;
use App\Matriculas\NovedadesObservador;
use App\Matriculas\FodaEstudiante;


use App\Core\Colegio;
use App\Sistema\Modelo;
use App\Sistema\SecuenciaCodigo;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class AcademicoDocenteController extends Controller
{
	
	public function __construct()
    {
		$this->middleware('auth');
    }
	
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $usuario = Auth::user();
        $colegio = Colegio::where('empresa_id',$usuario->empresa_id)->get()->first();

        if( !is_null($colegio) )
        {
            $periodo_lectivo = PeriodoLectivo::get_actual();
            $listado = AsignacionProfesor::get_asignaturas_x_curso( $usuario->id, $periodo_lectivo->id );
             
            $miga_pan = [
                            ['url'=>'NO','etiqueta'=>'Académico docente']
                        ];   

            $modelo_logros_id = Modelo::where('modelo','sga_logros')->get()->first()->id;

            $modelo_logros_adicionales_id = Modelo::where('modelo','sga_logros_adicionales')->get()->first()->id;

            $modelo_plan_clases_id = Modelo::where('modelo','PlanClaseEncabezado')->get()->first()->id;
            $modelo_guia_academica_id = Modelo::where('modelo','sga_guias_academicas')->get()->first()->id;

            return view('academico_docente.index',compact('listado','miga_pan','modelo_logros_id','periodo_lectivo','modelo_plan_clases_id', 'modelo_guia_academica_id','modelo_logros_adicionales_id'));
        }else{
            echo "La Empresa asociada al Usuario actual no tiene ningún Colegio asociado.";
        }
    }

    /**
     * Show the form for creating a LOGROS.
     *
     * @return \Illuminate\Http\Response
     */
    public function ingresar_logros( $curso_id, $asignatura_id)
    {
        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find( Input::get('id_modelo') );

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

        // Se Personalizan los campos
        for ($i=0; $i < count($lista_campos) ; $i++) { 
            
            switch ( $lista_campos[$i]['name'] ) {
                case 'curso_id':
                    $curso = Curso::find($curso_id);
                    $lista_campos[$i]['opciones'] = [$curso_id => $curso->descripcion];
                    break;
                case 'asignatura_id':
                    $asignatura = Asignatura::find($asignatura_id);
                    $lista_campos[$i]['opciones'] = [$asignatura_id => $asignatura->descripcion];
                    break;
                
                default:
                    # code...
                    break;
            }                
        }

        $archivo_js = app($modelo->name_space)->archivo_js;

        $form_create = [
                        'url' => json_decode( app( $modelo->name_space )->urls_acciones )->store,
                        'campos' => $lista_campos
                    ];

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Ingresar logros']
                    ];

        return view('layouts.create',compact('form_create','miga_pan','archivo_js'));
    }

    // Muestra listado de logros para revisar y editar
    public function revisar_logros($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $modelo = Modelo::find( Input::get('id_modelo') );
        
        $registros = app( $modelo->name_space )->get_logros( $colegio->id, $curso_id, $asignatura_id );

        $miga_pan = [
                        ['url' => 'academico_docente?id='.Input::get('id'), 'etiqueta' => 'Académico docente'],
                        ['url' => 'NO', 'etiqueta' => $modelo->descripcion ]
                    ];

        $modelo = Modelo::find( Input::get('id_modelo') );

        $encabezado_tabla = app($modelo->name_space)->encabezado_tabla;
        $titulo_tabla = '';
        $url_print = '';
        $url_ver = '';
        $url_estado = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');

        $urls_acciones = json_decode( app( $modelo->name_space )->urls_acciones );

        $url_crear = '';
        
        $url_edit = 'academico_docente/modificar_logros/'.$curso_id.'/'.$asignatura_id.'/id_fila'.$variables_url;

        $url_eliminar = '';
        if( $modelo->id == 70 )
        {
            $url_eliminar = 'academico_docente/eliminar_logros/'.$curso_id.'/'.$asignatura_id.'/id_fila'.$variables_url;
        }
        

        return view('layouts.index', compact('registros','miga_pan','url_crear','titulo_tabla','encabezado_tabla','url_edit','url_print','url_ver','url_estado','url_eliminar'));
    }


    // Muestra formulario para modificar logros
    public function modificar_logros( $curso_id, $asignatura_id, $logro_id )
    {
        
        $registro = Logro::find( $logro_id );

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find( Input::get('id_modelo') );

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','edit');

        $url_update = json_decode( app( $modelo->name_space )->urls_acciones )->update;

        $url_action = str_replace('id_fila', $logro_id, $url_update);

        $form_create = [
                        'url' => $url_action,
                        'campos' => $lista_campos
                    ];


        // NO se usa el ModeloController para cambiar la $miga_pan
        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Modificar logros']
                    ];

        return view('layouts.edit',compact('form_create','miga_pan','registro','url_action'));
    }


    // Elimina un logro
    public function eliminar_logros( $curso_id, $asignatura_id, $logro_id )
    {
        $logro = Logro::find($logro_id);

        // Validación #1
        $periodo = Periodo::find( $logro->periodo_id );
        if ( $periodo->cerrado )
        {
            return redirect('academico_docente/revisar_logros/'.$curso_id.'/'.$asignatura_id.'?id='.Input::get('id'))->with('mensaje_error','Logro no puede ser eliminado, El periodo está cerrado. Código Logro: '.$logro->codigo );
        }

        // Validación #2
        $periodo_lectivo = PeriodoLectivo::find( $periodo->periodo_lectivo_id );
        if ( $periodo_lectivo->cerrado )
        {
            return redirect( 'academico_docente/revisar_logros/'.$curso_id.'/'.$asignatura_id.'?id='.Input::get('id') )->with('mensaje_error','Logro no puede ser eliminado, El PERIODO LECTIVO está cerrado. Código Logro: '.$logro->codigo );
        }

        $logro->delete();

        return redirect('academico_docente/revisar_logros/'.$curso_id.'/'.$asignatura_id.'?id='.Input::get('id'))->with('flash_message','Logro Eliminado correctamente.');
    }



    //Selección de datos para calificar
    public function calificar1($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];

        $periodos = Periodo::opciones_campo_select();

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($asignatura_id);

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Ingresar calificaciones']
                    ];

        return view('academico_docente.create_calificacion',compact('periodos','asignatura','curso','miga_pan'));
    }


    public function revisar_calificaciones($curso_id, $asignatura_id)
    {
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get()[0];
        
        $registros = Calificacion::get_calificaciones( $colegio->id, $curso_id, $asignatura_id );

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Calificaciones']
                    ];

        $encabezado_tabla=['Año','Periodo','Curso','Estudiante','Asignatura','Calificación',''];

        return view('layouts.index', compact('registros','encabezado_tabla','miga_pan'));
    }



    public function revisar_estudiantes($curso_id,$id_asignatura)
    {
        // Se obtienen los estudiantes con matriculas activas en el curso y año indicado
        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, PeriodoLectivo::get_actual()->id, 'Activo' );

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($id_asignatura);

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Estudiantes']
                    ];

        return view('academico_docente.revisar_estudiantes', compact('estudiantes','curso','asignatura','miga_pan'));
    }

    public function listar_estudiantes($curso_id,$id_asignatura)
    {
        // Se obtienen los estudiantes con matriculas activas en el curso y año indicado
        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, PeriodoLectivo::get_actual()->id, 'Activo' );

        $curso = Curso::find($curso_id);
        $asignatura = Asignatura::find($id_asignatura);
        $docente = Auth::user()->name;
        
        $view =  View::make('academico_docente.pdf_estudiantes1', compact('estudiantes','curso','asignatura','docente'))->render();
        $orientacion='landscape';

        //crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper('Letter',$orientacion);
        return $pdf->download('listado_estudiantes.pdf');

    }

    // FORMULARIOS PARA ACTUALIZAR  ASPECTOS
    public function valorar_aspectos_observador($id_estudiante)
    {
        $estudiante = Estudiante::get_datos_basicos( $id_estudiante );
        $tipos_aspectos = TiposAspecto::all();
        $novedades = NovedadesObservador::where('id_estudiante',$id_estudiante)->get();
        $registros_analisis = FodaEstudiante::where('id_estudiante',$id_estudiante)->get();

        $miga_pan = [
                ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                ['url'=>'academico_docente/revisar_estudiantes/curso_id/'.Input::get('curso_id').'/id_asignatura/'.Input::get('asignatura_id').'?id='.Input::get('id'),'etiqueta'=>'Estudiantes'],
                ['url'=>'NO','etiqueta'=>'Observador: Valoración de aspectos > '.$estudiante->nombre_completo ]
            ];

        return view('academico_docente.estudiantes.valorar_aspectos_observador',compact('tipos_aspectos','estudiante','novedades','registros_analisis','miga_pan'));
    }

    // PROCEDIMIENTO ALMACENAR ASPECTOS
    public function guardar_valoracion_aspectos(Request $request)
    {
        $estudiante = Estudiante::find($request->id_estudiante);
        $tipos_aspectos = TiposAspecto::all();
        
        $aspectos = CatalogoAspecto::all();
        for($i=0;$i<count($aspectos);$i++) {
            $aspecto_estudiante=AspectosObservador::where('id_aspecto','=',$request->input('id_aspecto.'.$i))->where('id_estudiante','=',$request->id_estudiante)->where('fecha_valoracion','like',date('Y').'%')->count();
            if($aspecto_estudiante==0){
                DB::insert('insert into sga_aspectos_observador 
                        (id_estudiante,id_aspecto,fecha_valoracion,valoracion_periodo1,valoracion_periodo2,valoracion_periodo3,valoracion_periodo4) values (?,?,?,?,?,?,?)',
                        [$request->id_estudiante,$request->input('id_aspecto.'.$i),$request->fecha_valoracion,$request->input('valoracion_periodo1.'.$i),$request->input('valoracion_periodo2.'.$i),$request->input('valoracion_periodo3.'.$i),$request->input('valoracion_periodo4.'.$i)]);

            }else{
                DB::table('sga_aspectos_observador')->where(['id'=>$request->input('aspecto_estudiante_id.'.$i)])->update(['valoracion_periodo1'=>$request->input('valoracion_periodo1.'.$i),'valoracion_periodo2'=>$request->input('valoracion_periodo2.'.$i),
                                    'valoracion_periodo3'=>$request->input('valoracion_periodo3.'.$i),'valoracion_periodo4'=>$request->input('valoracion_periodo4.'.$i)]);
            }
        }

        return redirect('academico_docente/valorar_aspectos_observador/'.$estudiante->id.'?id='.$request->url_id.'&curso_id='.$request->curso_id.'&asignatura_id='.$request->asignatura_id)->with('flash_message','Registros actualizados correctamente.');
    }



    /**
        ** Vista previa del observador del estudiante.
    **/
    public function show_observador($id)
    {

        $view_pdf = ObservadorEstudianteController::vista_preliminar($id,'show');

        $miga_pan = [
                ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                ['url'=>'academico_docente/revisar_estudiantes/curso_id/'.Input::get('curso_id').'/id_asignatura/'.Input::get('asignatura_id').'?id='.Input::get('id'),'etiqueta'=>'Estudiantes'],
                ['url'=>'NO','etiqueta'=>'Observador: Visualización']
            ];

        return view( 'academico_docente.estudiantes.observador_show',compact('miga_pan','view_pdf','id') );
    }

}