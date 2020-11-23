<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;


use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Matriculas\MatriculaController;

use DB;
use PDF;
use View;
use Auth;
use Storage;
use Input;

use App\Core\Empresa;
use App\Core\Tercero;
use App\Sistema\Modelo;
use App\Sistema\ModelAction;
use App\Core\TipoDocumentoId;
use App\Sistema\SecuenciaCodigo;

// Modelos
use App\Matriculas\Inscripcion;
use App\Matriculas\Estudiante;
use App\Matriculas\Curso;
use App\Matriculas\Grado;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Boletin;

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

        if ( is_null($tercero) )
        {
            $empresa_id = 1;
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

        // Almacenar datos restantes de la inscripcion
        $registro_creado->codigo = $codigo;
        $registro_creado->core_tercero_id = $tercero->id;
        $registro_creado->estado = 'Pendiente';
        $registro_creado->save();

        // se llama la vista de show
        return redirect( 'matriculas/inscripcion/'.$registro_creado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo );
	}

	public function show($id)
    {
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
      $pdf = \App::make('dompdf.wrapper');
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

        return View::make('matriculas.formatos.inscripcion1',compact('inscripcion','descripcion_transaccion','empresa','vista') )->render();
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
        if ( !is_null($estudiante) )
        {
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('mensaje_error','La incripción ya tiene matrícula registrada. No puede ser eliminada.');
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