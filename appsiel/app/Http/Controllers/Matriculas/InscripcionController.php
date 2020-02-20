<?php

namespace App\Http\Controllers\Matriculas;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;


use App\Http\Controllers\Sistema\ModeloController;

use DB;
use PDF;
use View;
use Auth;
use Storage;
use Input;

use App\Core\Empresa;
use App\Core\Tercero;
use App\Sistema\Modelo;
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
        SecuenciaCodigo::incrementar_consecutivo( 'inscripciones' );//where(['modulo'=>'inscripciones', 'estado'=>'Activo' ])->increment('consecutivo');

        // Se almacena el tercero. ADVERTENCIA DATOS MANUALES (si se hace la inscripcion desde la página web, validar cómo se almacena la empresa si no es un usuario logueado)
        
        // OJO!!!!! Datos manuales
        $tipo = 'Persona natural';


        $tercero = Tercero::create( array_merge($request->all(),
                                    [   'codigo_ciudad' => $request->codigo_ciudad, 
                                        'core_empresa_id' => Auth::user()->empresa_id, 
                                        'descripcion' => $request->nombre1." ".$request->otros_nombres." ".$request->apellido1." ".$request->apellido2, 
                                        'tipo' => $tipo, 
                                        'estado' => 'Activo'] ) );

        // Almacenar datos restantes de la inscripcion
        $registro_creado->codigo = $codigo;
        $registro_creado->core_tercero_id = $tercero->id;
        $registro_creado->save();

        // se llama la vista de show
        return redirect( 'matriculas/inscripcion/'.$registro_creado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo );
	}

	public function show($id)
    {
		$reg_anterior = Inscripcion::where('id', '<', $id)->max('id');
        $reg_siguiente = Inscripcion::where('id', '>', $id)->min('id');

        $view_pdf = InscripcionController::vista_preliminar($id,'show');

        $miga_pan = $this->get_miga_pan( $this->modelo, 'Consulta');

        return view( 'matriculas.show_inscripcion',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') );
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



	public function edit($id)
    {
		// Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $estudiante = Estudiante::get_estudiante_x_tercero_id( $registro->core_tercero_id );

        // Si el tercero es un Estudiante, entonces ya tiene matrícula y su inscripción no se puede modificar.
        if ( !is_null( $estudiante ) ) {
            //print_r($registro);
            return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('mensaje_error','La incripción ya tiene matrículas asociadas. No puede ser modificada. Estudiante: '.$estudiante->nombre_completo);
        }else{

            $tercero = Tercero::find( $registro->core_tercero_id );

            $lista_campos = ModeloController::get_campos_modelo($modelo,$registro,'edit');

            //Personalización de la lista de campos
            for ($i=0; $i < count($lista_campos) ; $i++) { 

                switch ($lista_campos[$i]['name']) {
                    case 'nombre1':
                        $lista_campos[$i]['value'] = $tercero->nombre1;
                        break;
                    case 'otros_nombres':
                        $lista_campos[$i]['value'] = $tercero->otros_nombres;
                        break;
                    case 'apellido1':
                        $lista_campos[$i]['value'] = $tercero->apellido1;
                        break;
                    case 'apellido2':
                        $lista_campos[$i]['value'] = $tercero->apellido2;
                        break;
                    case 'id_tipo_documento_id':
                        $lista_campos[$i]['value'] = $tercero->id_tipo_documento_id;
                        break;
                    case 'numero_identificacion':
                        $lista_campos[$i]['value'] = $tercero->numero_identificacion;
                        break;
                    case 'direccion1':
                        $lista_campos[$i]['value'] = $tercero->direccion1;
                        break;
                    case 'telefono1':
                        $lista_campos[$i]['value'] = $tercero->telefono1;
                        break;
                    case 'email':
                        $lista_campos[$i]['value'] = $tercero->email;
                        break;
                    case 'codigo_ciudad':
                        $lista_campos[$i]['value'] = $tercero->codigo_ciudad;
                        break;
                    
                    default:
                        # code...
                        break;
                }      
                
            }

            // Agregar NUEVO campo con el core_tercero_id
            $lista_campos[$i]['tipo'] = 'hidden';
            $lista_campos[$i]['name'] = 'core_tercero_id';
            $lista_campos[$i]['descripcion'] = '';
            $lista_campos[$i]['opciones'] = [];
            $lista_campos[$i]['value'] = $tercero->id;
            $lista_campos[$i]['atributos'] = [];
            $lista_campos[$i]['requerido'] = false;

            // form_create para generar un formulario html 
            $form_create = [
                            'url' => $modelo->url_form_create,
                            'campos' => $lista_campos
                        ];

            $url_action = $modelo->url_form_create.'/'.$id;

            $miga_pan = $this->get_miga_pan( $modelo, $registro->descripcion);

            // Si el modelo tiene un archivo js particular
            $archivo_js = app($modelo->name_space)->archivo_js;

            return view('layouts.edit',compact('form_create','miga_pan','registro','archivo_js','url_action'));
        }
    }
	
	public function update(Request $request, $id)
	{
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);


        $tercero = Tercero::find( $registro->core_tercero_id );
        $descripcion = $request->nombre1.' '.$request->otros_nombres.' '.$request->apellido1.' '.$request->apellido2;
        $datos = array_merge( $request->all(), [ 'descripcion' => $descripcion ] );

        $tercero->fill( $datos );
        $tercero->save();


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

        // Borrar tercero 
        $tercero = Tercero::find($registro->core_tercero_id);        
        $tercero->delete();

        //Borrar Inscripción
        $registro->delete();


        return redirect( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Inscripción ELIMINADA correctamente. Código: '.$registro->codigo);
    }
}