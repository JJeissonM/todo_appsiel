<?php

namespace App\Http\Controllers\ActividadesEscolares;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\ImagenController;

use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;

use App\Calificaciones\Asignatura;

use App\Cuestionarios\ActividadEscolar;
use App\Cuestionarios\Cuestionario;
use App\Cuestionarios\RespuestaCuestionario;

use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Core\Acl;

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

        /*
        // Se agrega el permiso a la tabla ACL, la actividad solo estará visible para el usuario que la crea y para el superadministrador (ID = 1)
        $datos = [  
                    'modelo_recurso_id' => $request->url_id_modelo,
                    'recurso_id' => $registro->id,
                    'user_id' => Auth::user()->id,
                    'permiso_denegado' => 0
                ];

        Acl::create( $datos );

        // Se crean dos registros en la tabla ACL
        if ( Auth::user()->id !=1 ) {
            Acl::create( array_merge( $datos, ['user_id' => 1] ) );
        }
        */   

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

        $registro->fill( $request->all() );
        $registro->save();
        
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
    public function ver_actividad( $actividad_id )
    {
        $app = Aplicacion::find(Input::get('id'));
        $modelo = Modelo::find(Input::get('id_modelo'));

        $actividad = ActividadEscolar::find( $actividad_id );

        if ( is_null( $actividad ) )
        {
            $actividad = (object)[ 'id' => 0, 'curso_id' => 0, 'asignatura_id' => 0, 'descripcion' => '', 'cuestionario_id' => 0 ];
        }

        $periodo_lectivo = PeriodoLectivo::get_actual();

        $estudiantes = Matricula::estudiantes_matriculados( $actividad->curso_id, $periodo_lectivo->id, 'Activo' );

        $miga_pan = [
                ['url'=> $app->app.'?id='.Input::get('id'), 'etiqueta'=> $app->descripcion ],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion],
                ['url'=>'NO','etiqueta'=> $actividad->descripcion]
            ];

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

        $asignatura = Asignatura::find( $actividad->asignatura_id );

        if ( is_null( $asignatura ) )
        {
            $asignatura = (object)[ 'id' => 0, 'descripcion' => '' ];
        }

        return view('calificaciones.actividades_escolares.ver_actividad',compact( 'actividad','cuestionario', 'preguntas','miga_pan','modelo','respuestas','estudiantes','estudiante', 'asignatura', 'actividad_id') );
        
    }

    /**
     * Vista Para la Aplicación Académico Estudiante
     *
     */
    public function hacer_actividad( $actividad_id )
    {
        $user = Auth::user();

        // Si se terminó la sesión
        if ( is_null( $user ) )
        {
            return redirect('inicio');
        }
        
        $estudiante = Estudiante::where('user_id', $user->id )->get()->first();

        $modelo = Modelo::where('modelo', 'actividades_escolares')->get()->first();

        $actividad = ActividadEscolar::find( $actividad_id );

        if ( is_null( $actividad ) )
        {
            $actividad = (object)[ 'id' => 0, 'curso_id' => 0, 'asignatura_id' => 0, 'descripcion' => '', 'cuestionario_id' => 0 ];
        }

        $curso = Curso::find( $actividad->curso_id );
        if (is_null($curso) )
        {
            $curso = (object)['id'=>0,'descripcion'=>''];
        }

        $asignatura = Asignatura::find( $actividad->asignatura_id );
        if (is_null($asignatura) )
        {
            $asignatura = (object)['id'=>0,'descripcion'=>''];
        }

        $miga_pan = [
                ['url'=>'academico_estudiante?id='.Input::get('id'),'etiqueta'=>'Académico estudiante'],
                ['url'=>'mis_asignaturas/'.$curso->id.'?id='.Input::get('id'),'etiqueta'=>'Mis Asignaturas: ' . $curso->descripcion],
                ['url'=>'academico_estudiante/actividades_escolares/'.$curso->id.'/'.$actividad->asignatura_id . '?id='.Input::get('id'),'etiqueta'=>'Actividades escolares: ' . $asignatura->descripcion ],
                ['url'=>'NO','etiqueta' => $actividad->descripcion ]
            ];

        //$calificacion = (object) array('calificacion' => 0);

        $cuestionario = (object)[];
        $preguntas = (object)[];
        $respuestas = (object)['id'=>0,'respuesta_enviada'=>'','calificacion'=>'','updated_at'=>''];

        if ( $actividad->cuestionario_id > 0 ) 
        {
            $cuestionario = Cuestionario::find($actividad->cuestionario_id);
            $preguntas = $cuestionario->preguntas()->orderBy('orden')->get();


            $respuestas = RespuestaCuestionario::where(['actividad_id'=>$actividad->id,'estudiante_id'=>$estudiante->id,'cuestionario_id'=>$actividad->cuestionario_id])->get()->first();

            if( is_null( $respuestas ) )
            {   
                $respuestas = (object)['id'=>0,'respuesta_enviada'=>'','calificacion'=>'','updated_at'=>''];
            }
        }

        // Para actividades sin cuestionario
        $respuesta = RespuestaCuestionario::where( [ 'actividad_id' => $actividad->id, 'estudiante_id' => $estudiante->id ] )->get()->first();

        if ( is_null( $respuesta ) )
        {
            $respuesta = (object)['id'=>0,'respuesta_enviada'=>'','calificacion'=>'','adjunto'=>'','updated_at'=>''];
        }

        return view('calificaciones.actividades_escolares.hacer_actividad',compact('actividad','cuestionario', 'preguntas','miga_pan','estudiante','modelo','respuestas','respuesta','asignatura','actividad_id'));        
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

        return redirect('actividades_escolares/hacer_actividad/'.$request->actividad_id.'?id=6')->with('flash_message','Respuestas guardadas correctamente.');
        
    }


    public function sin_cuestionario_guardar_respuesta(Request $request)
    {
        $request['respuesta_enviada'] = $request->respuesta_enviada_2;
        
        if ( $request->respuesta_id == 0)
        {
            // Crear nuevo registro
            $respuesta = RespuestaCuestionario::create( $request->all() );
            $respuesta_id = $respuesta->id;
        }else{
            // Actualizar registro anterior
            $respuesta = RespuestaCuestionario::find($request->respuesta_id);
            $respuesta->fill( $request->all() );
            $respuesta->save();

            $respuesta_id = $request->respuesta_id;
        }

        if( $request->hasFile('adjunto') )
        {
            $nombre_archivo = ImagenController::guardar_imagen_en_disco( $request->adjunto, 'img/adjuntos_respuestas_estudiantes/' );

            $respuesta = RespuestaCuestionario::find( $respuesta_id );
            $respuesta->adjunto = $nombre_archivo;
            $respuesta->save();

        }

        return redirect( 'actividades_escolares/hacer_actividad/'.$request->actividad_id.'?id='.Input::get('id') )->with('flash_message','¡Respuesta almacenada correctamente!');
        
    }

    public function remover_archivo_adjunto( $respuesta_id )
    {
        
        $respuesta = RespuestaCuestionario::find( $respuesta_id );

        // Se borra el archivo del disco
        Storage::delete( 'img/adjuntos_respuestas_estudiantes/' . $respuesta->adjunto );

        // Actualizar registro
        $respuesta->update( [ 'adjunto' => '' ] );

        return redirect( 'actividades_escolares/hacer_actividad/'.$respuesta->actividad_id.'?id='.Input::get('id') )->with('flash_message','¡Se retiró el archivo adjunto de la respuesta!');
        
    }

    public function almacenar_calificacion_a_respuesta_estudiante()
    {
        $respuesta_id = Input::get('respuesta_id');

        $datos = [
                    'estudiante_id' => Input::get('estudiante_id'),
                    'actividad_id' => Input::get('actividad_id'),
                    'cuestionario_id' => 0,
                    'calificacion' => Input::get('valor_nuevo')
                ];

        if ( $respuesta_id == 0 && Input::get('valor_nuevo') != '' )
        {
            // Crear nuevo registro
            $respuesta = RespuestaCuestionario::create( $datos );
            $respuesta_id = $respuesta->id;
        }else{
            // actualizar registro anterior
            $respuestas = RespuestaCuestionario::find($respuesta_id);

            if( is_null( $respuestas )  )
            {
                return $respuesta_id;
            }
            
            $respuestas->fill( $datos );
            $respuestas->save();
        }

        return $respuesta_id;
        
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



    public function get_options_actividades_escolares( $curso_id, $asignatura_id, $user_id )
    {
        $array_wheres = [ 
                            [ 'curso_id', '=', $curso_id ],
                            [ 'asignatura_id', '=', $asignatura_id ]
                        ];

        $user = Auth::user();

        if( $user->hasRole('Profesor') || $user->hasRole('Director de grupo') )
        {
            $array_wheres = array_merge($array_wheres, [ [ 'created_by', '=', $user_id] ]);
        }

        $opciones = ActividadEscolar::where( $array_wheres )->get();
        
        $select = '<option value="">Seleccionar... </option>';
        foreach ($opciones as $opcion)
        {
            $select .= '<option value="'.$opcion->id.'">'.$opcion->descripcion.'</option>';
        }

        return $select;
    }

}