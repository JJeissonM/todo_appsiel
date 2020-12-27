<?php

namespace App\Http\Controllers\AcademicoDocente;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Input;

use App\Matriculas\Curso;
use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;

use App\Calificaciones\Asignatura;
use App\Calificaciones\AsistenciaClase;

use App\Core\Colegio;

class AsistenciaClaseController extends Controller
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
        $colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

        $id_colegio=$colegio->id;

        $registros = AsistenciaClase::consultar_registros( null, Input::get('curso_id'), Input::get('asignatura_id') );

        $curso = Curso::find(Input::get('curso_id'));
        $asignatura = Asignatura::find( Input::get('asignatura_id') );

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'NO','etiqueta'=>'Asistencia clases / Curso: '.$curso->descripcion.' / Asignatura: '.$asignatura->descripcion]
                    ];
        
        $titulo_tabla='';

        $encabezado_tabla=['Fecha','Estudiante','Curso','Asignatura','Asistió?','Anotación','Acción'];

        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&curso_id='.Input::get('curso_id').'&asignatura_id='.Input::get('asignatura_id');

        $url_crear = 'academico_docente/asistencia_clases/create'.$variables_url;

        $url_edit = 'academico_docente/asistencia_clases/id_fila/edit'.$variables_url;

        $url_eliminar= '';

        return view('layouts.index', compact('registros','miga_pan','url_crear','titulo_tabla','encabezado_tabla','url_edit','url_eliminar'));
                
    }

    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {        
        $curso = Curso::find( Input::get('curso_id') );
        $asignatura = Asignatura::find( Input::get('asignatura_id') );


        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'academico_docente/asistencia_clases?curso_id='.Input::get('curso_id').'&asignatura_id='.Input::get('asignatura_id').'&id='.Input::get('id'),'etiqueta'=>'Asistencia clases / Curso: '.$curso->descripcion.' / Asignatura: '.$asignatura->descripcion],
                        ['url'=>'NO','etiqueta'=>'Tomar asistencia']
                    ];

        return view('academico_docente.asistencia_clases.crear',compact('curso','asignatura','miga_pan'));
    }

    public function continuar_creacion(Request $request)
    {
        
        $registros = Matricula::estudiantes_matriculados( $request->curso_id, PeriodoLectivo::get_actual()->id, 'Activo'  );

        $fecha = $request->fecha;
        $curso = Curso::find($request->curso_id);
        $asignatura = Asignatura::find($request->asignatura_id);
        
        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'academico_docente/asistencia_clases?curso_id='.Input::get('curso_id').'&asignatura_id='.Input::get('asignatura_id').'&id='.Input::get('id'),'etiqueta'=>'Asistencia clases / Curso: '.$curso->descripcion.' / Asignatura: '.$asignatura->descripcion],
                        ['url'=>'NO','etiqueta'=>'Tomar asistencia']
                    ];

        return view('academico_docente.asistencia_clases.create',compact('registros','fecha','curso','asignatura','miga_pan'));
    }
   

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $curso = Curso::find($request->curso_id);
        $asignatura = Asignatura::find($request->asignatura_id);
        
        // Guardar la asistencia para cada estudiante      
        for($i=0;$i<$request->cantidad_estudiantes;$i++){
            $asistencia = new AsistenciaClase;
            $asistencia->id_estudiante = $request->input('id_estudiante.'.$i);
            $asistencia->curso_id = $request->curso_id;
            $asistencia->asignatura_id = $request->asignatura_id;
            $asistencia->fecha = $request->fecha;
            $asistencia->asistio = $request->input('asistio-'.$i);
            $asistencia->anotacion = $request->input('anotacion.'.$i);
            $asistencia->save();
        }

        return redirect('academico_docente/asistencia_clases?curso_id='.$request->curso_id.'&asignatura_id='.$request->asignatura_id.'&id='.$request->id_app )->with('flash_message','Asistencias ingresadas correctamente'); 

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $registro = AsistenciaClase::find($id);

        $curso = Curso::find($registro->curso_id);
        $asignatura = Asignatura::find($registro->asignatura_id);

        $miga_pan = [
                        ['url'=>'academico_docente?id='.Input::get('id'),'etiqueta'=>'Académico docente'],
                        ['url'=>'academico_docente/asistencia_clases?curso_id='.Input::get('curso_id').'&asignatura_id='.Input::get('asignatura_id').'&id='.Input::get('id'),'etiqueta'=>'Asistencia clases / Curso: '.$curso->descripcion.' / Asignatura: '.$asignatura->descripcion],
                        ['url'=>'NO','etiqueta'=>'Modificar asistencia '.Estudiante::get_nombre_completo( $registro->id_estudiante ) ]
                    ];

        return view('academico_docente.asistencia_clases.edit',compact('registro','miga_pan','curso','asignatura'));
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
        $curso = Curso::find($request->curso_id);
        $asignatura = Asignatura::find($request->asignatura_id);

        $registro = AsistenciaClase::findOrFail($id);

        $registro->fill($request->all())->save();

        return redirect('academico_docente/asistencia_clases?curso_id='.$request->curso_id.'&asignatura_id='.$request->asignatura_id.'&id='.$request->id_app )->with('flash_message','Asistencia MODIFICADA correctamente');
    }

}
