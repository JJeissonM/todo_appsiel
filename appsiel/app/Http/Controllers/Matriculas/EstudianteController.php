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

use App\Sistema\Modelo;
use App\Sistema\Html\Boton;

use App\User;

// Modelos
use App\Matriculas\Matricula;
use App\Matriculas\PeriodoLectivo;
use App\Matriculas\Estudiante;
use App\Matriculas\Grado;
use App\Matriculas\Curso;
use App\Calificaciones\Asignatura;
use App\Calificaciones\Boletin;
use App\Core\Colegio;
use App\Core\Tercero;

class EstudianteController extends ModeloController
{	
    protected $modelo;

	public function edit($id)
    {
		// Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

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
                case 'barrio':
                    $lista_campos[$i]['value'] = $tercero->barrio;
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

        $miga_pan = $this->get_miga_pan($modelo,$registro->descripcion);

        // Si el modelo tiene un archivo js particular
        $archivo_js = app($modelo->name_space)->archivo_js;

        return view('layouts.edit',compact('form_create','miga_pan','registro','archivo_js','url_action'));
        
    }

    /*
        Visualizar los datos de un estudiante
    */
    public function show( $estudiante_id )
    {
        $estudiante = Estudiante::get_datos_basicos( $estudiante_id );// Se obtiene el registro del modelo indicado y el anterior y siguiente registro
        
        $registro = app($this->modelo->name_space)->find($estudiante_id);
        $reg_anterior = app($this->modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($this->modelo->name_space)->where('id', '>', $registro->id)->min('id');
        
        $miga_pan = $this->get_miga_pan($this->modelo,$registro->descripcion);

        $url_crear = '';
        $url_edit = '';
        
        $id_transaccion = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
        if ($this->modelo->url_crear!='') {
            $url_crear = $this->modelo->url_crear.$variables_url;    
        }
        if ($this->modelo->url_edit!='') {
            $url_edit = $this->modelo->url_edit.$variables_url;
        }

        // ENLACES
        $botones = [];
        if ( $this->modelo->enlaces != '') 
        {
            $enlaces = json_decode( $this->modelo->enlaces );
            $i=0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
            }
        }
        
        return view('matriculas.estudiantes.show',compact('miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente','botones','estudiante')); 
    }
	
	/**
	 * Muestra formulario para generar listados de estudiantes
	 *
	 */	
    public function listar()
    {
		$colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
        $colegio = $colegio[0];

		$periodos_lectivos = PeriodoLectivo::get_array_activos();


		$registros = Grado::where(['id_colegio'=>$colegio->id,'estado'=>'Activo'])
				->get();
		$grados['Todos'] = 'Todos';
		foreach ($registros as $fila) {
			$grados[$fila->id] = $fila->descripcion;
		}

		$miga_pan = [
            ['url'=>'matriculas?id='.Input::get('id'),'etiqueta'=>'Matrículas'],
            ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Estudiantes'],
            ['url'=>'NO','etiqueta'=>'Listados']
        ];
		
		return view('matriculas.estudiantes.listar',compact('periodos_lectivos','grados','miga_pan'));
    }
	
    /**
	 * Muestra formulario para importar estudiantes
	 *
	 */	
    public function importar_excel()
    {
		return view('matriculas.estudiantes.importar_excel.index');
    }
	
	/**
	 * Genera en PDF listados de estudiantes. Además, actualiza los datos al modificar un estudiante.
	 *
	 */	
    public function update(Request $request, $id)
	{        
        $estudiantes = [];
        
		switch($id){
			case 'listado':

				if ( $request->sga_grado_id == "Todos") {
					$grados = Grado::where('estado','Activo')->get();				
				}else{
					// Un grado específico
					$grados = Grado::where('id',$request->sga_grado_id)->get();
				}

				$i=0;
				foreach ($grados as $fila_grado) {

					$grado = Grado::find($fila_grado->id);

					if ( $request->curso_id == "Todos" ) {
						$cursos = Curso::where('sga_grado_id',$grado->id)->where('estado','Activo')->get();
					}else{						
						$cursos = Curso::where('id',$request->curso_id)->get();
					}
					
					foreach ($cursos as $fila_curso) 
					{
						$estudiantes[$i]['grado'] = $grado->descripcion;

						$curso = Curso::find($fila_curso->id);
						$estudiantes[$i]['curso'] = $curso->descripcion;
						
						$estudiantes[$i]['listado'] = Matricula::estudiantes_matriculados( $curso->id, $request->periodo_lectivo_id, null );
						$i++;
					}
				}
				
				$orientacion = $request->orientacion;

				/*
					Formato 1 = Listado por asignaturas
					Formato 2 = Ficha Datos básicos
					Formato 3 = Lista Datos básicos
					Formato 4 = Lista de usuarios
				*/
				$formato = 'pdf_estudiantes'.$request->tipo_listado;

            	$tam_letra=$request->tam_letra;
				
				$view =  View::make('matriculas/estudiantes/'.$formato, compact('estudiantes','tam_letra') )->render();
				
				//crear PDF
				$pdf = \App::make('dompdf.wrapper');
				$pdf->loadHTML(($view))->setPaper($request->tam_hoja,$orientacion);

				return $pdf->download('listado_estudiantes.pdf');

				break;

			default:

				// Para cualquier $id (cualquier estudiante), se actualizan los datos en las tablas respectivas: terceros, users, estudiantes
				$estudiante = Estudiante::find($id);


		        $registro2 = '';
		        // Si se envían datos tipo file
		        //if ( count($request->file()) > 0)
		        if( !empty( $request->file() ) )
		        {   
		            // Copia identica del registro del modelo, pues cuando se almacenan los datos cambia la instancia
		            $registro2 = $estudiante;
		        }

				$tercero = Tercero::find( $estudiante->core_tercero_id );	

				$descripcion = $request->nombre1.' '.$request->otros_nombres.' '.$request->apellido1.' '.$request->apellido2;
				$datos = array_merge($request->all(),[
	                        'descripcion' => $descripcion ] );
        
        		$tercero->fill( $datos );
		        $tercero->save();

		        $usuario = User::find( $estudiante->user_id );
		        if( is_null( $usuario ) )
		        {
                    $name = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
                    $email = $request->email;
		        	$usuario = User::crear_y_asignar_role( $name, $email, 4); // 4 = Role Estudiante
		        	$mensaje = '<br> Se creó un nuevo usuario para el estudiante. <br> Puede acceder al sistema con los siguientes datos: <br> email: '. $request->email.' <br> Contraseña: colombia1';
		        }else{
		        	$usuario->name = $descripcion;
		        	$usuario->email = $request->email;
		        	$usuario->save();
		        	$mensaje = '';
		        }

		        $estudiante->fill( $datos );
		        $estudiante->user_id = $usuario->id;
		        $estudiante->save();

        		if( isset( $request->imagen ) )
        		{
        			$modelo = Modelo::find( $request->url_id_modelo );
        			$general = new ModeloController;
		            $tercero->imagen = $general->almacenar_imagenes( $request, $modelo->ruta_storage_imagen, $registro2, 'edit' );
		        	$tercero->save();
		        }


		        return redirect( 'matriculas/estudiantes/show/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.'.$mensaje);

				break;
		}
	}	
	
    public static function nombre_curso($id_estudiante)
    {
		$curso_id = Matricula::where('id_estudiante',"=",$id_estudiante)->where('estado',"=",'Activo')->value('curso_id');
		

		if ( !is_null($curso_id) )
		{
			$curso = Curso::find($curso_id);
			$nombre_curso = $curso->descripcion;
		}else{
			$nombre_curso = "";
		}
		
		return $nombre_curso;
    }
	
    public static function nombre_acudiente($id_estudiante)
    {
		$nombre_acudiente=Matricula::where('id_estudiante',"=",$id_estudiante)->where('estado',"=",'Activo')->value('acudiente');
		return $nombre_acudiente;
    }

    public static function get_estudiantes_matriculados( $periodo_lectivo_id, $curso_id)
    {
        $registros = Matricula::estudiantes_matriculados( $curso_id, $periodo_lectivo_id, null );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($registros as $opcion)
        {
            $opciones .= '<option value="'.$opcion->id.'">'.$opcion->nombre_completo.'</option>';
        }

        return $opciones;
    }
}
