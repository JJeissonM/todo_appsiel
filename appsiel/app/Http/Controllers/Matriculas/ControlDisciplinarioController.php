<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Hash;
use PDF;
use Mail;
use View;
use Input;
use App\User;

use App\Matriculas\Matricula;
use App\Matriculas\Curso;
use App\Matriculas\Estudiante;
use App\Matriculas\PeriodoLectivo;

use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;
use App\Calificaciones\Calificacion;
use App\Calificaciones\Periodo;
use App\Calificaciones\Logro;
use App\AcademicoDocente\Asignacion;

use App\Core\SemanasCalendario;
use App\Core\Colegio;
use App\Sistema\Aplicacion;


use App\Matriculas\ControlDisciplinario;
use App\Matriculas\CodigoDisciplinario;


class ControlDisciplinarioController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $email_user = Auth::user()->email;

        // Guardar las registros de control disciplinario para cada estudiante
        for($i=0;$i<$request->cantidad_estudiantes;$i++)
        {           
            $datos = ['estudiante_id' => $request->input('estudiante.'.$i)] + 
                ['semana_id' => $request->semana_id] + 
                ['curso_id' => $request->curso_id] + 
                ['asignatura_id' => $request->asignatura_id] +
                ['codigo_1_id' => $request->input('codigo_1_id.'.$i)] + 
                ['codigo_2_id' => $request->input('codigo_2_id.'.$i)] + 
                ['codigo_3_id' => $request->input('codigo_3_id.'.$i)] + 
                ['observacion_adicional' => $request->input('observacion_adicional.'.$i)] + 
                ['estado' => 'Activo'] +
                ['creado_por' => $email_user] + 
                ['modificado_por' => ''] + 
                ['created_at' => date('Y-m-d H:i:s')] + 
                ['updated_at' => date('Y-m-d H:i:s')];

            ControlDisciplinario::create( $datos);
        }
        
        $semana = SemanasCalendario::find($request->semana_id);

        return redirect('matriculas/control_disciplinario/precreate/'.$request->aux_curso_id.'/'.$request->asignatura_id.'?id='.$request->app_id)->with('flash_message','Control discipinario ingresado correctamente. Semana: '.$semana->descripcion);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $email_user = Auth::user()->email;

        // Guardar las registros de control disciplinario para cada estudiante
        for($i=0;$i<$request->cantidad_estudiantes;$i++)
        {           
            if($request->input('control_id.'.$i) == 0)
            {
                $datos = ['estudiante_id' => $request->input('estudiante.'.$i)] + 
                    ['semana_id' => $request->semana_id] + 
                    ['curso_id' => $request->curso_id] + 
                    ['asignatura_id' => $request->asignatura_id] +
                    ['codigo_1_id' => $request->input('codigo_1_id.'.$i)] + 
                    ['codigo_2_id' => $request->input('codigo_2_id.'.$i)] + 
                    ['codigo_3_id' => $request->input('codigo_3_id.'.$i)] + 
                    ['observacion_adicional' => $request->input('observacion_adicional.'.$i)] + 
                    ['estado' => 'Activo'] +
                    ['creado_por' => $email_user] + 
                    ['modificado_por' => ''] + 
                    ['created_at' => date('Y-m-d H:i:s')] + 
                    ['updated_at' => date('Y-m-d H:i:s')];

                ControlDisciplinario::create( $datos);
            }else{
                ControlDisciplinario::where('id', $request->input('control_id.'.$i) )
                    ->update( ['codigo_1_id' => $request->input('codigo_1_id.'.$i)] + 
                    ['codigo_2_id' => $request->input('codigo_2_id.'.$i)] + 
                    ['codigo_3_id' => $request->input('codigo_3_id.'.$i)] + 
                    ['observacion_adicional' => $request->input('observacion_adicional.'.$i)] + 
                    ['modificado_por' => $email_user] );
            }
        }
        
        $semana = SemanasCalendario::find($request->semana_id);

        return redirect('matriculas/control_disciplinario/precreate/'.$request->aux_curso_id.'/'.$request->asignatura_id.'?id='.$request->app_id)->with('flash_message','Control discipinario ACTUALIZADO correctamente. Semana: '.$semana->descripcion);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    /* PARA EL ADADEMICO DOCENTE */
    //Selección de datos para Ingresar Registros del control disciplinario 
    public function precreate($curso_id, $asignatura_id)
    {
        $app = Aplicacion::find(Input::get('id'));

        $miga_pan = [
                        [ 'url' => $app->app.'?id='.Input::get('id'),'etiqueta'=>$app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Ingresar control disciplinario']
                    ];

        $semanas = SemanasCalendario::get_array_to_select();

        if ( $curso_id != 0) 
        {
            // Para usuarios Docentes
            $curso = Curso::find($curso_id);
            $asignatura = Asignatura::find($asignatura_id);

            return view('academico_docente.control_disciplinario.pre_create_docente',compact('semanas','asignatura','curso','miga_pan'));
        }else{
            // Para usuarios administradores
            $cursos = Curso::get_array_to_select();

            return view('academico_docente.control_disciplinario.pre_create_admin',compact('semanas','cursos','miga_pan'));
        }
            
    }

    /**
     * Formulario para ingresar los códigos y almacenar.
     *
     */
    public function crear(Request $request)
    {
        $app = Aplicacion::find( $request->id_app );

        $semana = SemanasCalendario::find($request->semana_id);

        $nom_curso = Curso::where('id','=',$request->curso_id)->value('descripcion');

        $nom_asignatura = Asignatura::where('id','=',$request->asignatura_id)->value('descripcion');

        $anio = explode("-", $semana->fecha_inicio);
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, PeriodoLectivo::get_actual()->id, 'Activo'  );

        $miga_pan = [
                        ['url' => $app->app.'?id='.$request->id_app,'etiqueta' => $app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Ingresar control disciplinario']
                    ];
        

        $opciones = CodigoDisciplinario::all();
        $codigos[''] = '';
        foreach ($opciones as $opcion)
        {
          $codigos[$opcion->id] = $opcion->id.' '.$opcion->descripcion;
        }

        // Verificar si ya tiene registros para los datos seleccionados
        $control = ControlDisciplinario::where(['semana_id'=>$semana->id,'curso_id'=>$request->curso_id,
                'asignatura_id'=>$request->asignatura_id])
                ->count();
        
        $aux_curso_id = $request->aux_curso_id;

        if( $control > 0 )
        {
            // SI ya tienen registros, se modifican
            $vec_estudiantes = array();
            $i=0;
            foreach($estudiantes as $estudiante)
            {
                $vec_estudiantes[$i]['id_estudiante'] = $estudiante->id_estudiante;

                $vec_estudiantes[$i]['nombre_completo'] = $estudiante->nombre_completo;

                $registro_control = ControlDisciplinario::where( [ 'semana_id' => $semana->id,'curso_id' => $request->curso_id,
                'asignatura_id' => $request->asignatura_id, 'estudiante_id' => $estudiante->id_estudiante ] )->get();
                
                $vec_estudiantes[$i]['control_id'] = 0;
                $vec_estudiantes[$i]['codigo_1_id'] = '';
                $vec_estudiantes[$i]['codigo_2_id'] = '';
                $vec_estudiantes[$i]['codigo_3_id'] = '';
                $vec_estudiantes[$i]['observacion_adicional'] = '';
                
                if( !is_null($registro_control) )
                {
                    $vec_estudiantes[$i]['control_id'] = $registro_control[0]->id;

                    $vec_estudiantes[$i]['codigo_1_id'] = $registro_control[0]->codigo_1_id;

                    $vec_estudiantes[$i]['codigo_2_id'] = $registro_control[0]->codigo_2_id;

                    $vec_estudiantes[$i]['codigo_3_id'] = $registro_control[0]->codigo_3_id;

                    $vec_estudiantes[$i]['observacion_adicional'] = $registro_control[0]->observacion_adicional;
                }
                
                $i++;
            }

            return view('academico_docente.control_disciplinario.edit',['vec_estudiantes'=>$vec_estudiantes,
                'cantidad_estudiantes'=>count($estudiantes),
                'semana'=>$semana,
                'curso_id'=>$request->curso_id,
                'nom_curso'=>$nom_curso,
                'asignatura_id'=>$request->asignatura_id,
                'nom_asignatura'=>$nom_asignatura,
                'miga_pan'=>$miga_pan,
                'codigos'=>$codigos,
                'aux_curso_id' => $aux_curso_id]);
        }else{
            // Si no tienen observaciones, se crean por primera vez
            return view('academico_docente.control_disciplinario.create',['estudiantes'=>$estudiantes,
                'semana'=>$semana,
                'curso_id'=>$request->curso_id,
                'nom_curso'=>$nom_curso,
                'asignatura_id'=>$request->asignatura_id,
                'nom_asignatura'=>$nom_asignatura,
                'miga_pan'=>$miga_pan,
                'codigos'=>$codigos,
                'aux_curso_id' => $aux_curso_id]);
        }
    }


    /* PARA EL ADADEMICO DOCENTE */
    // Consultar códigos disciplinario ingresados
    public function consultar_control_disciplinario($curso_id, $fecha)
    {
        
        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, PeriodoLectivo::get_actual()->id, 'Activo'  );

        $opciones = SemanasCalendario::where('estado','=','Activo')->orderBy('numero')->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id.'a3p0'.$opcion->fecha_inicio]=$opcion->numero.") ".$opcion->descripcion;
        }

        $semanas = $vec;

        $semana_actual = SemanasCalendario::where('fecha_inicio','<=',$fecha)->orderBy('fecha_inicio','DESC')->first();

        if (!isset($semana_actual->id)) 
        {
            $semana_actual = (object)['id'=>0,'descripcion'=>''];
        }

        $curso = Curso::find($curso_id);
        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($curso_id, null, PeriodoLectivo::get_actual()->id );


        $tabla =  View::make('academico_docente.control_disciplinario.tabla_estudiantes', compact('estudiantes','semana_actual','asignaturas','curso'))->render();


        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Consulta control disciplinario']
                    ];

        return view('academico_docente.control_disciplinario.consultar',compact('estudiantes','semanas','semana_actual','asignaturas','curso','tabla','miga_pan'));
    }

    // IMPRIMIR PDF códigos disciplinario ingresados
    public function imprimir_control_disciplinario($curso_id, $fecha)
    {
        
        $estudiantes = Matricula::estudiantes_matriculados( $curso_id, PeriodoLectivo::get_actual()->id, 'Activo'  );

        $semana_actual = SemanasCalendario::where('fecha_inicio','<=',$fecha)->orderBy('fecha_inicio','DESC')->first();

        if (!isset($semana_actual->id)) 
        {
            $semana_actual = (object)['id'=>0,'descripcion'=>''];
        }

        $curso = Curso::find($curso_id);
        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso($curso_id, null, PeriodoLectivo::get_actual()->id);

        $tabla =  '<h4 style="text-align:center;">Control académico y disciplinario</h4>
        <p><b>Curso: </b>'.$curso->descripcion.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Semana: </b>'.$semana_actual->descripcion.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Año: </b>'.explode("-",$semana_actual->fecha_inicio)[0].'</p>'.View::make('academico_docente.control_disciplinario.tabla_estudiantes', compact('estudiantes','semana_actual','asignaturas','curso'))->render();

        // Se prepara el PDF
        $orientacion='landscape';
        $tam_hoja = 'Folio';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML($tabla)->setPaper($tam_hoja,$orientacion);

        return $pdf->download('control_disciplinario.pdf');//stream();
    }

    // Para los administradores

    // Consultar códigos disciplinario ingresados
    public function consultar_control_disciplinario2()
    {
        $app = Aplicacion::find( Input::get('id') );

        $opciones = SemanasCalendario::where('estado','=','Activo')->orderBy('numero')->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id.'a3p0'.$opcion->fecha_inicio]=$opcion->numero.") ".$opcion->descripcion;
        }

        $semanas = $vec;

        $cursos = Curso::get_array_to_select();

        $miga_pan = [
                        ['url'=> $app->app.'?id='.Input::get('id'),'etiqueta'=> $app->descripcion],
                        ['url'=>'NO','etiqueta'=>'Consulta control disciplinario']
                    ];

        return view('academico_docente.control_disciplinario.consultar_admin',compact('semanas','cursos','miga_pan'));
    }

    public function ajax_consultar_control_disciplinario2(Request $request)
    {
        $app = Aplicacion::find( Input::get('id') );

        $semana_actual = SemanasCalendario::where('id',explode('a3p0',$request->semana_id)[0] )->get();
        

        if (!isset($semana_actual[0])) 
        {
            $semana_actual = (object)['id'=>0,'descripcion'=>'','fecha_inicio'=>'1900-01-01'];
        }else{
            $semana_actual = $semana_actual[0];
        }

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, PeriodoLectivo::get_actual()->id, 'Activo'  );

        $curso = Curso::find($request->curso_id);
        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $request->curso_id, null, PeriodoLectivo::get_actual()->id );

        return View::make('academico_docente.control_disciplinario.tabla_estudiantes', compact('estudiantes','semana_actual','asignaturas','curso'))->render();
    }
}
