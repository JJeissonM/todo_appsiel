<?php

namespace App\Http\Controllers\Matriculas;

use App\Core\Colegio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use App\Core\Empresa;
use App\Core\Tercero;
use App\Sistema\Modelo;

use App\Sistema\SecuenciaCodigo;

// Modelos
use App\Matriculas\Inscripcion;
use App\Matriculas\Estudiante;
use App\Matriculas\Responsableestudiante;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class InscripcionesEnLineaController extends Controller
{
    public function index()
    {
        return redirect( 'inscripciones_en_linea/create' );
    }

    public function create()
    {
        $modelo = Modelo::find( 323 );
        $lista_campos = ModeloController::get_campos_modelo($modelo, '', 'create');

        $form_create = [
            'url' => 'inscripciones_en_linea',
            'campos' => $lista_campos
        ];

        $miga_pan = [
            [ 
              'url' => url('/'),
              'etiqueta' => 'Inicio'
              ],
            [ 
              'url' => 'NO',
              'etiqueta' => 'Matrículas'
              ],
            [ 
              'url' => 'NO',
              'etiqueta' => 'Inscripción en línea'
              ]
            ];

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($modelo->name_space)->archivo_js;

        $vista_create = json_decode(app($modelo->name_space)->vistas)->create;
        
        return view($vista_create, compact('form_create', 'miga_pan', 'archivo_js'));
    }
	
	/**
	 * Guardar un nuevo estudiante
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function store(Request $request)
	{
        $datos = $request->all();
        
        $datos['email'] = $datos['email2'];
        $datos['numero_identificacion'] = $datos['numero_identificacion2'];

        $inscripcion = Inscripcion::create( $datos );

		// Se genera el Código
        $codigo = SecuenciaCodigo::get_codigo( 'inscripciones', (object)['grado_id'=>$request->sga_grado_id] );

        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo( 'inscripciones' );

        $tercero = Tercero::where( 'numero_identificacion', $request->numero_identificacion2 )->get()->first();
        
        $empresa_id = Empresa::get()->first()->id;

        if ( $tercero == null )
        {
            
            // OJO!!!!! Datos manuales
            $tipo = 'Persona natural';

            $tercero = Tercero::create( array_merge($datos,
                                        [   'codigo_ciudad' => $request->codigo_ciudad, 
                                            'core_empresa_id' => $empresa_id, 
                                            'email' => $request->email2, 
                                            'numero_identificacion' => $request->numero_identificacion2, 
                                            'descripcion' => $request->nombre1." ".$request->otros_nombres." ".$request->apellido1." ".$request->apellido2, 
                                            'tipo' => $tipo, 
                                            'estado' => 'Activo'] ) );
        }else{
            $tercero->fill( $datos );
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
        $inscripcion->codigo = $codigo;
        $inscripcion->core_tercero_id = $tercero->id;
        $inscripcion->core_tercero_id = $tercero->id;
        $inscripcion->estado = 'Pendiente';
        $inscripcion->origen = 'Página web';
        $inscripcion->save();

        $this->crear_datos_padres_y_acudiente($request,$empresa_id,$estudiante->id);

        // se llama la vista de show
        return redirect( 'inscripciones_en_linea/' . $inscripcion->id )->with('flash_message', config('matriculas.mensaje_inscripcion_creada'));
	}

    public function crear_datos_padres_y_acudiente($request,$empresa_id,$estudiante_id)
    {
        $datos_generales = [
            'codigo_ciudad' => $request->codigo_ciudad,
            'id_tipo_documento_id' => 13,
            'core_empresa_id' => $empresa_id, 
            'tipo' => 'Persona natural', 
            'estado' => 'Activo'
        ];

        if ($request->cedula_mama != '') {
            $mama = Tercero::where( 'numero_identificacion', $request->cedula_mama )->get()->first();
            if ($mama == null) {
                $mama = Tercero::create( array_merge( $datos_generales, [
                                            'email' => $request->email_mama, 
                                            'numero_identificacion' => $request->cedula_mama, 
                                            'descripcion' => $request->mama, 
                                            'nombre1' => $request->mama, 
                                            'telefono1' => $request->telefono_mama, 
                                            'direccion1' => $request->direccion_mama
                                        ] ) );
            }
            
            Responsableestudiante::create( [
                'tiporesponsable_id' => 2,
                'estudiante_id' => $estudiante_id,
                'tercero_id' => $mama->id
            ] );
        }
        
        if ($request->cedula_papa != '') {
            $papa = Tercero::where( 'numero_identificacion', $request->cedula_papa )->get()->first();
            if ($papa == null) {
                $papa = Tercero::create( array_merge( $datos_generales, [
                                            'email' => $request->email_papa, 
                                            'numero_identificacion' => $request->cedula_papa, 
                                            'descripcion' => $request->papa, 
                                            'nombre1' => $request->papa, 
                                            'telefono1' => $request->telefono_papa,
                                            'direccion1' => $request->direccion_papa
                                        ] ));
            }
                            
            Responsableestudiante::create( [
                'tiporesponsable_id' => 1,
                'estudiante_id' => $estudiante_id,
                'tercero_id' => $papa->id
            ] );
        }
        
        if ($request->cedula_acudiente != '') {
            $acudiente = Tercero::where( 'numero_identificacion', $request->cedula_acudiente )->get()->first();
            if ($acudiente == null) {
                $acudiente = Tercero::create( array_merge( $datos_generales, [
                                            'email' => $request->email_acudiente, 
                                            'numero_identificacion' => $request->cedula_acudiente, 
                                            'descripcion' => $request->acudiente, 
                                            'nombre1' => $request->acudiente, 
                                            'telefono1' => $request->telefono_acudiente,
                                            'direccion1' => $request->direccion_acudiente
                                        ] ));
            }
        }

        switch ($request->acudiente_seleccionado) {
            case 'madre':
                $acudiente_tercero_id = $mama->id;
                break;
            
            case 'padre':
                $acudiente_tercero_id = $papa->id;
                break;
            
            case 'otro':
                $acudiente_tercero_id = $acudiente->id;
                break;
            
            default:
                # code...
                break;
        }

        Responsableestudiante::create( [
            'tiporesponsable_id' => 3,
            'estudiante_id' => $estudiante_id,
            'tercero_id' => $acudiente_tercero_id
        ] );

    }

	public function show($id)
    {
        if ($id == 'print') {
            return $this->inscripcion_print(Input::get('id'));
        }

        $arr = explode(':',$id);
        if (isset($arr[1])) {
            $tercero = Tercero::where('numero_identificacion',(int)$arr[1])->get()->first();

            if ($tercero == null) {
                return redirect( 'inscripciones_en_linea/create')->with('mensaje_error','Lo sentimos. La inscripción que quiere consultar no existe.');
            }else{
                $inscripcion = Inscripcion::where('core_tercero_id',$tercero->id)->get()->first();
                if ($inscripcion == null) {
                    return redirect( 'inscripciones_en_linea/create')->with('mensaje_error','Lo sentimos. La inscripción que quiere consultar no existe.');
                }
                $id = $inscripcion->id;
            } 
        }      

        $view_pdf = $this->vista_preliminar($id,'show');
        
        $miga_pan = [
            [ 
              'url' => url('/'),
              'etiqueta' => 'Inicio'
              ],
            [ 
              'url' => 'NO',
              'etiqueta' => 'Matrículas'
              ],
            [ 
              'url' => 'NO',
              'etiqueta' => 'Inscripción en línea'
              ]
            ];

        return view( 'matriculas.inscripciones.en_linea.show',compact('miga_pan','view_pdf','id') );
    }

    public function inscripcion_print($id)
    {
      $view_pdf = $this->vista_preliminar($id,'imprimir');

      $tam_hoja = 'Letter';
      $orientacion='landscape';
      $pdf = App::make('dompdf.wrapper');
      $pdf->loadHTML(($view_pdf))->setPaper($tam_hoja,$orientacion);
      return $pdf->stream('inscripcion.pdf');
    }  

    // Generar vista para SHOW  o IMPRIMIR
    public function vista_preliminar($id,$vista)
    {
    	// UNIFICAR ESTAS TRES CONSULTAS EN UNA SOLA
        $inscripcion = Inscripcion::get_registro_impresion( $id );

        $empresa = Empresa::get()->first();
        $descripcion_transaccion = 'Ficha de Inscripción';

        $estudiante = $inscripcion->estudiante();

        return View::make('matriculas.formatos.inscripcion1',compact('inscripcion','descripcion_transaccion','empresa','vista','estudiante') )->render();
    }

}