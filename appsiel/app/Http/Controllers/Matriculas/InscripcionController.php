<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;


use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Matriculas\MatriculaController;

use App\Core\Empresa;
use App\Core\Tercero;
use App\Sistema\Modelo;

use App\Sistema\SecuenciaCodigo;

// Modelos
use App\Matriculas\Inscripcion;
use App\Matriculas\Estudiante;

use App\Core\Colegio;
use App\Core\ModeloEavValor;
use App\Matriculas\Matricula;
use App\Matriculas\Services\ResponsablesEstudiantesService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class InscripcionController extends ModeloController
{	
	
    public function crear_matricula( $id_inscripcion )
    {
        $matricula = new MatriculaController;

        $request = new Request;
        $request['id_inscripcion'] = $id_inscripcion;

        return $matricula->crear_nuevo( $request );
    }
	
	/**
	 * Guardar un nuevo estudiante
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function store(Request $request)
	{
		$general = new ModeloController;
        $registro_creado = $general->crear_nuevo_registro( $request );

		// Se genera el Código
        $codigo = SecuenciaCodigo::get_codigo( 'inscripciones', (object)['grado_id'=>$request->sga_grado_id] );

        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo( 'inscripciones' );

        $tercero = Tercero::where( 'numero_identificacion', $request->numero_identificacion2 )->get()->first();

        $empresa_id = 1;

        if ( is_null($tercero) )
        {
            $user = Auth::user();
            if ( !is_null($user) )
            {
                $empresa_id = $user->empresa_id;
            }
            
            // OJO!!!!! Datos manuales
            $tipo = 'Persona natural';

            $tercero = Tercero::create( array_merge($request->all(),
                                        [   'codigo_ciudad' => $request->codigo_ciudad, 
                                            'core_empresa_id' => $empresa_id, 
                                            'email' => $request->email2, 
                                            'numero_identificacion' => $request->numero_identificacion2, 
                                            'descripcion' => $request->nombre1." ".$request->otros_nombres." ".$request->apellido1." ".$request->apellido2, 
                                            'tipo' => $tipo, 
                                            'estado' => 'Activo'] ) );
        }else{
            $tercero->fill( $request->all() );
            $tercero->save();
        }

        $estudiante = Estudiante::where('core_tercero_id',$tercero->id)->get()->first();
        if ( $estudiante == null )
        {
            $estudiante = Estudiante::create( [
                                            'id_colegio' => Colegio::get()->first()->id, 
                                            'core_tercero_id' => $tercero->id, 
                                            'genero' => $request->genero,
                                            'fecha_nacimiento' => $request->fecha_nacimiento, 
                                            'ciudad_nacimiento' => $request->ciudad_nacimiento
                                        ] );
        }

        // Almacenar datos restantes de la inscripcion
        $registro_creado->codigo = $codigo;
        $registro_creado->core_tercero_id = $tercero->id;
        $registro_creado->estado = 'Pendiente';
        $registro_creado->origen = 'Interna';
        $registro_creado->save();

        (new ResponsablesEstudiantesService())->crear_datos_padres_y_acudiente($request,$empresa_id,$estudiante->id);

        // se llama la vista de show
        return redirect( 'matriculas/inscripcion/' . $registro_creado->id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo )->with('flash_message','Registro CREADO correctamente.');
	}

	public function show($id)
    {
        $inscripcion = Inscripcion::find($id);

        $estudiante = Estudiante::where('core_tercero_id',$inscripcion->core_tercero_id)->get()->first();
        if ( $estudiante == null )
        {
            $estudiante = Estudiante::create( [
                                'id_colegio' => Colegio::get()->first()->id, 
                                'core_tercero_id' => $inscripcion->core_tercero_id, 
                                'genero' => $inscripcion->genero,
                                'fecha_nacimiento' => $inscripcion->fecha_nacimiento, 
                                'ciudad_nacimiento' => $inscripcion->ciudad_nacimiento
                            ] );
        }

		$reg_anterior = Inscripcion::where('id', '<', $id)->max('id');
        $reg_siguiente = Inscripcion::where('id', '>', $id)->min('id');

        $view_pdf = InscripcionController::vista_preliminar($id,'show');
        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=0';
        
        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $miga_pan = $this->get_miga_pan( $this->modelo, 'Consulta');

        return view( 'matriculas.inscripciones.show',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id','acciones') );
    }

    public function inscripcion_print($id)
    {
      $view_pdf = InscripcionController::vista_preliminar($id,'imprimir');

      $tam_hoja = 'Letter';
      $orientacion='portrait';
      $pdf = App::make('dompdf.wrapper');
      $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
      return $pdf->stream('inscripcion.pdf');
    }  

    // Generar vista para SHOW  o IMPRIMIR
    public static function vista_preliminar($id,$vista)
    {
    	// UNIFICAR ESTAS TRES CONSULTAS EN UNA SOLA
        $inscripcion = Inscripcion::get_registro_impresion( $id );

        $empresa = Empresa::find( Auth::user()->empresa_id );
        $descripcion_transaccion = 'Ficha de Inscripción';

        $estudiante = $inscripcion->estudiante();

        /**
         * 323 = ID Modelo de Inscripciones en linea
         */
        $string_ids_campos = '323-' . $inscripcion->id . '-core_campo_id-1570';
        $estudiante->es_de_inclusion = ModeloEavValor::get_valor_campo( $string_ids_campos );

        $string_ids_campos = '323-' . $inscripcion->id . '-core_campo_id-1571';
        $estudiante->diagnostico_inclusion = ModeloEavValor::get_valor_campo( $string_ids_campos );

        $formato = 'formatos.inscripciones.estandar';
        if ( !in_array(config('matriculas.formato_default_fichas_incripcion_y_matricula'), [null,'']) ) {
            $formato = 'formatos.inscripciones.' . config('matriculas.formato_default_fichas_incripcion_y_matricula');
        }

        return View::make( 'matriculas.' . $formato,compact('inscripcion','descripcion_transaccion','empresa','vista', 'estudiante') )->render();
    }
	
	public function update(Request $request, $id)
	{
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $descripcion = $request->nombre1.' '.$request->otros_nombres.' '.$request->apellido1.' '.$request->apellido2;
        $datos = array_merge( $request->all(), [ 'descripcion' => $descripcion ] );
        $datos['numero_identificacion'] = $request->numero_identificacion2;
        $datos['email'] = $request->email2;

        $registro->tercero->fill( $datos );
        $registro->tercero->save();

        $registro->fill( $request->all() );
        $registro->save();

        return redirect('matriculas/inscripcion/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
	}

    public function creacion_masiva()
    {
        $id_modelo = 66; //Inscripciones

        $modelo = Modelo::find($id_modelo);

        $datos = [
                    
                ];

        for ($i=0; $i < count($datos); $i++) {
            echo $datos[$i]['nombre1']."<br/>";
            $registro_creado = app($modelo->name_space)->create( $datos[$i] );

            // Se genera el Código
            $codigo = SecuenciaCodigo::get_codigo( 'inscripciones', (object)['grado_id'=>$datos[$i]['sga_grado_id'] ] );

            // Se incrementa el consecutivo
            SecuenciaCodigo::where('modulo','inscripciones')
                                        ->where('estado','Activo')
                                        ->increment('consecutivo');


            // Se almacena el tercero. ADVERTENCIA DATOS MANUALES (si se hace la inscripcion desde la página web, validar cómo tomo la empresa si no es un usuario logueado)
            $datos2 = array_merge($datos[$i],
                            [ 'id_tipo_documento_id' => 11, 
                            'codigo_ciudad' => '16920001', 
                            'core_empresa_id' => 1, 
                            'descripcion' => $datos[$i]['nombre1']." ".$datos[$i]['otros_nombres']." ".$datos[$i]['apellido1']." ".$datos[$i]['apellido2'], 
                            'tipo' => 1 ] );
            
            $tercero = Tercero::create( $datos2 );

            // Almacenar datos restantes de la inscripcion
            $registro_creado->codigo = $codigo;
            $registro_creado->core_tercero_id = $tercero->id;
            $registro_creado->save();
        }      

    }

    /**
     * Eliminar INSCRIPIÓN Y TERCERO.
     *
     * 
     */
    public function eliminar($id)
    {
        $registro = Inscripcion::find($id);

        // Verificación 1: Si es un estudiante, ya está matriculado
        $estudiante = Estudiante::get_estudiante_x_tercero_id( $registro->core_tercero_id );

        // Si el tercero es un Estudiante, entonces ya tiene matrícula y su inscripción no se puede eliminar.
        if ( $estudiante != null)
        {
            $matricula = Matricula::where([
                ['id_estudiante', '=', $estudiante->id]
            ])
                ->get()
                ->first();

            if ( $matricula != null)
            {
                return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('mensaje_error','La incripción ya tiene matrícula registrada. No puede ser eliminada.');
            }

            // Estudiante No tiene matriculas, continuo
            (new ResponsablesEstudiantesService())->delete_datos_padres_y_acudiente($estudiante->id);
        }

        //Borrar Inscripción
        $registro->delete();

        // Borrar tercero 
        $tercero = Tercero::find( $registro->core_tercero_id );

        if ( $tercero->validar_eliminacion( $tercero->id ) == 'ok' )
        {
            $tercero->delete();
        }

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Inscripción ELIMINADA correctamente. Código: '.$registro->codigo);
    }
}