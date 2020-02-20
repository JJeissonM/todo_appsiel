<?php

namespace App\Http\Controllers\ActividadesEscolares;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Auth;
use DB;
use Hash;
use Mail;
use View;
use Input;
use App\User;

use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;

use App\Cuestionarios\ActividadEscolar;
use App\Cuestionarios\Cuestionario;
use App\Cuestionarios\RespuestaCuestionario;

//use App\Cuestionarios\ActividadEscolar;

use App\Core\Colegio;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Acl;

use App\AcademicoDocente\EstudianteTieneActividadEscolar;

//Importing laravel-permission models
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

//Enables us to output flash messaging
use Session;

class ActividadesEscolaresController extends ModeloController
{
	protected $colegio;


    /*

        NOTA: Cuando el usuario no tiene role de profesor, no le está asignacdo bien los curso. Revisar function ajustar_opciones_select() en actividades.js

    */


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $registro = $this->crear_nuevo_registro( $request );


        $periodo_lectivo = PeriodoLectivo::get_segun_periodo( $request->periodo_id );

        // Ahora se actualiza el registro de actividades para cada estudiante del curso
        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo_lectivo->id, 'Activo' );

        foreach ($estudiantes as $fila)
        {
            EstudianteTieneActividadEscolar::create([
                                                        'estudiante_id' => $fila->id,
                                                        'actividad_escolar_id' => $registro->id
                                                    ]);
        }


        // Se agrega el permiso a la tabla ACL, la actividad solo estará visible para el usuario que la crea y para el superadministrador (ID = 1)
        $datos = [  
                    'modelo_recurso_id' => $request->url_id_modelo,
                    'recurso_id' => $registro->id,
                    'user_id' => Auth::user()->id,
                    'permiso_denegado' => 0
                ];

        Acl::create( $datos );

        if ( Auth::user()->id !=1 ) {
            Acl::create( array_merge( $datos, ['user_id' => 1] ) );
        }     

        $this->almacenar_imagenes( $request, $this->modelo->ruta_storage_imagen, $registro );

        return redirect('actividades_escolares/ver_actividad/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro CREADO correctamente.');
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
        /*
          * No se deberían borrar todas las respuestas si solo se quiere cambiar un detalle en la actividad, ejemplo fecha de entrega para ampliar el plazo
          */
        $modelo = Modelo::find($request->url_id_modelo);
        $registro = ActividadEscolar::find( $id );

        $registro2 = '';

        if( !empty( $request->file() ) )
        {   
            // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
            $registro2 = $registro;
        } 

        //$periodo_lectivo = PeriodoLectivo::get_segun_periodo( $request->periodo_id );
            $periodo_lectivo = PeriodoLectivo::get_actual();

        // Borrar todas las asignaciones anteriores de la actividad
        EstudianteTieneActividadEscolar::where('actividad_escolar_id',$id)->delete();

        // Borrar todas las respuestas ingresadas por los estudiantes para esa actividad
        RespuestaCuestionario::where('actividad_id',$id)->delete();

        $estudiantes = Matricula::estudiantes_matriculados( $request->curso_id, $periodo_lectivo->id, 'Activo' );

        foreach ($estudiantes as $fila) 
        {
            EstudianteTieneActividadEscolar::create([
                                                    'estudiante_id' => $fila->id,
                                                    'actividad_escolar_id' => $registro->id
                                                ]);        
        }

        
        $this->almacenar_imagenes( $request, $modelo->ruta_storage_imagen, $registro2, 'edit' );


        return redirect('actividades_escolares/ver_actividad/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','registro MODIFICADO correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     * Esta función se llama desde: calificaciones.actividades_escolares.ver_actividad
     */
    public function eliminar_actividad(Request $request)
    {
        $actividad = ActividadEscolar::find($request->recurso_a_eliminar_id);

        // Borrar todas las asignaciones a estudiantes anteriores de la actividad
        EstudianteTieneActividadEscolar::where('actividad_escolar_id',$actividad->id)->delete();

        // Borrar todas las respuestas ingresadas por los estudiantes para esa actividad
        RespuestaCuestionario::where('actividad_id',$actividad->id)->delete();

        // Borrar registros ACL del usuario para esa actividad
        $wheres = [  
                    'modelo_recurso_id' => Input::get('id_modelo'),
                    'recurso_id' => $actividad->id,
                    'user_id' => Auth::user()->id
                ];
        Acl::where( $wheres )->delete( );
        Acl::where( array_merge( $wheres, ['user_id' => 1] ) )->delete( );

        $actividad->delete();

        return redirect('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('mensaje_error','Actividad ELIMINADA correctamente.');
    }

    /**
     * Vista Para la Aplicación Académico Docente
     *
     */
    public function ver_actividad($actividad_id)
    {
        $app = Aplicacion::find(Input::get('id'));
        $modelo = Modelo::find(Input::get('id_modelo'));

        $actividad = ActividadEscolar::find($actividad_id);

        $estudiantes = Matricula::estudiantes_matriculados( $actividad->curso_id, null, 'Activo' );

        //$reg_anterior = ActividadEscolar::where('id', '<', $actividad->id)->max('id');
        //$reg_siguiente = ActividadEscolar::where('id', '>', $actividad->id)->min('id');

        $miga_pan = [
                ['url'=> $app->app.'?id='.Input::get('id'), 'etiqueta'=> $app->descripcion ],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion],
                ['url'=>'NO','etiqueta'=> $actividad->descripcion]
            ];

        //$calificacion = (object) array('calificacion' => 0);

        $cuestionario = (object)[];
        $preguntas = (object)[];
        $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];

        if ($actividad->cuestionario_id > 0 ) 
        {
            $cuestionario = Cuestionario::find($actividad->cuestionario_id);
            $preguntas = $cuestionario->preguntas()->orderBy('orden')->get();


            $respuestas = RespuestaCuestionario::where(['actividad_id'=>$actividad->id,'estudiante_id'=>0,'cuestionario_id'=>$actividad->cuestionario_id])->get();

            if( !empty( $respuestas->toArray() ) )
            {   
                // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
                $respuestas = $respuestas[0];
            }else{
                $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];
            }

        }    

        $estudiante = (object)['id'=>0];

        return view('calificaciones.actividades_escolares.ver_actividad',compact('actividad','cuestionario', 'preguntas','miga_pan','modelo','respuestas','estudiantes','estudiante'));
        
    }

    /**
     * Vista Para la Aplicación Académico Estudiante
     *
     */
    public function hacer_actividad($actividad_id)
    {
        $estudiante = Estudiante::where('user_id', Auth::user()->id)->get()->first();

        $modelo = Modelo::where('modelo', 'actividades_escolares')->get()->first();

        $actividad = ActividadEscolar::find($actividad_id);

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'academico_estudiante/actividades_escolares?id='.Input::get('id'),'etiqueta'=>'Actividades escolares'],
                ['url'=>'NO','etiqueta' => $actividad->descripcion ]
            ];

        //$calificacion = (object) array('calificacion' => 0);

        $cuestionario = (object)[];
        $preguntas = (object)[];
        $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];

        if ($actividad->cuestionario_id > 0 ) 
        {
            $cuestionario = Cuestionario::find($actividad->cuestionario_id);
            $preguntas = $cuestionario->preguntas()->orderBy('orden')->get();


            $respuestas = RespuestaCuestionario::where(['actividad_id'=>$actividad->id,'estudiante_id'=>$estudiante->id,'cuestionario_id'=>$actividad->cuestionario_id])->get()->first();

            if( is_null( $respuestas ) )
            {   
                $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];
            }
        }   

        return view('calificaciones.actividades_escolares.hacer_actividad',compact('actividad','cuestionario', 'preguntas','miga_pan','estudiante','modelo','respuestas'));        
    }

    /**
     * Guardar Respuestas del cuestionario
     *
     */
    public function guardar_respuesta(Request $request)
    {
        if ( $request->respuesta_id == 0) {
            // Crear nuevo registro
            $respuestas = RespuestaCuestionario::create( $request->all() );
        }else{
            // actualizar registro anterior
            $respuestas = RespuestaCuestionario::find($request->respuesta_id);
            $respuestas->fill( $request->all() );
            $respuestas->save();
        }   

        return redirect('actividades_escolares/hacer_actividad/'.$request->actividad_id)->with('flash_message','Respuestas guardadas correctamente.');
        
    }



    /**
     * Para que el profesor visualice los resultados de un estudiante
     *
     */
    public function visualizar_resultados_estudiante($cuestionario_id, $estudiante_id)
    {
        $estudiante = Estudiante::find($estudiante_id);

        $cuestionario = (object)[];
        $preguntas = (object)[];
        $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];

        $cuestionario = Cuestionario::find( $cuestionario_id );
        $preguntas = $cuestionario->preguntas()->orderBy('orden')->get();

        $respuestas = RespuestaCuestionario::where(['estudiante_id'=>$estudiante_id,'cuestionario_id'=>$cuestionario_id])->get();

        if( !empty( $respuestas->toArray() ) )
        {   
            $respuestas = $respuestas[0];
        }else{
            $respuestas = (object)['id'=>0,'respuesta_enviada'=>''];
        }

        return View::make('calificaciones.actividades_escolares.resultados_cuestionario',compact('cuestionario', 'preguntas','estudiante','respuestas'))->render();        
    }
}