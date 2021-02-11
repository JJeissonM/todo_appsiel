<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;

use Auth;
use DB;
use Input;
use Storage;

use App\User;

use App\Sistema\Modelo;
use App\Core\Tercero;

class ProfesionalSaludController extends Controller
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $general = new ModeloController();

        return $general->create();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $general = new ModeloController();

        // Almacenar datos del Profesional de la salud
        $registro_creado = $general->crear_nuevo_registro( $request );

        /* Almacenar datos del Tercero y asignar al Profesional*/
        $tercero = Tercero::crear_nuevo_tercero($general, $request);
        $registro_creado->core_tercero_id = $tercero->id;
        $registro_creado->save();

        // Crear Como usuario del sistema y asociar al Tercero
        $name = $request->nombre1 . " " . $request->otros_nombres . " " . $request->apellido1 . " " . $request->apellido2;
        $email = $request->email;
        $password = str_random(7);
        $user = User::crear_y_asignar_role( $name, $email, 14, $password ); // 14 = Role "Profesional Salud"
        
        if ( is_null( $user ) )
        {
            $user_id = 0;
        }else{
            $user_id = $user->id;
        }

        $tercero->user_id = $user_id;
        $tercero->save();

        return redirect( 'consultorio_medico/profesionales/'.$registro_creado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente. DATOS DE ACCESO > Usuario: ' . $email . ' / Contraseña: ' . $password );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $general = new ModeloController();

        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro del modelo indicado y el anterior y siguiente registro
        $registro = app($modelo->name_space)->find($id);
        $reg_anterior = app($modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($modelo->name_space)->where('id', '>', $registro->id)->min('id');
        
        // Se obtienen los campos asociados a ese modelo, con sus valores
        $lista_campos1 = $modelo->campos()->orderBy('orden')->get();
        $lista_campos_paciente = $general->asignar_valores_de_campo_al_registro($modelo, $registro, $lista_campos1->toArray() );

        // Para el tercero asociado al paciente
        $modelo_tercero = Modelo::where('modelo','terceros')->first();
        $registro2 = $registro->tercero;
        $lista_campos2 = $modelo_tercero->campos()->orderBy('orden')->get();
        $lista_campos_tercero = $general->asignar_valores_de_campo_al_registro($modelo_tercero, $registro2, $lista_campos2->toArray() );

        $cantidad_campos = count($lista_campos_paciente);
        $cantidad_campos2 = count($lista_campos_tercero);

        // Array auxiliar para visualizar los datos del paciente. Donde la keys del array son los valores name y el valor es el value
        $lista_campos = [];

        for ($i=0; $i < $cantidad_campos; $i++) 
        {
            
            $lista_campos[ $lista_campos_paciente[$i]['name'] ] = $lista_campos_paciente[$i]['value'];

            for ($j=0; $j < $cantidad_campos2; $j++) 
            {
                if ( $lista_campos_paciente[$i]['name'] == $lista_campos_tercero[$j]['name'] ) 
                {
                    $lista_campos[ $lista_campos_paciente[$i]['name'] ] = $lista_campos_tercero[$j]['value'];
                }
            }
        }
        
        //dd( $lista_campos );
        
        $miga_pan = $general->get_miga_pan($modelo,$registro->descripcion);

        $url_crear = '';
        $url_edit = '';
        $url_print = '';
        $url_ver = '';
        $url_custom = '';
        $url_eliminar = '';

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        if ($modelo->url_crear!='') {
            $url_crear = $modelo->url_crear.$variables_url;    
        }
        if ($modelo->url_edit!='') {
            $url_edit = $modelo->url_edit.$variables_url;
        }
        if ($modelo->url_print!='') {
            $url_print = $modelo->url_print.$variables_url;
        }
        if ($modelo->url_ver!='') {
            $url_ver = $modelo->url_ver.$variables_url;
        }
        if ($modelo->url_custom!='') {
            $url_custom = $modelo->url_custom.$variables_url;
        }
        if ($modelo->url_eliminar!='') {
            $url_eliminar = $modelo->url_eliminar.$variables_url;
        }
        
        return view( 'consultorio_medico.profesionales_show', compact('lista_campos','miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente') );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $general = new ModeloController();

        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        $lista_campos = $general->get_campos_modelo($modelo,$registro,'edit');

        //$paciente = new Paciente;
        $registro->nombre1 = $registro->tercero->nombre1;
        $registro->otros_nombres = $registro->tercero->otros_nombres;
        $registro->apellido1 = $registro->tercero->apellido1;
        $registro->apellido2 = $registro->tercero->apellido2;
        $registro->id_tipo_documento_id = $registro->tercero->id_tipo_documento_id;
        $registro->numero_identificacion = $registro->tercero->numero_identificacion;
        $registro->direccion1 = $registro->tercero->direccion1;
        $registro->telefono1 = $registro->tercero->telefono1;
        $registro->email = $registro->tercero->email;
        
        //
        //dd( array_merge(  $registro->getOriginal(), $registro->tercero->getOriginal() ) );
        
        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $url_action = 'web/'.$id;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        //$miga_pan = $general->get_miga_pan($modelo,$registro->descripcion);

        $miga_pan = [
                        ['url'=>'consultorio_medico?'.Input::get('id'),'etiqueta'=>'Consultorio Médico'],
                        ['url'=>'consultorio_medico/profesionales?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion],
                        ['url'=>'NO','etiqueta'=> $registro->tercero->nombre1." ".$registro->tercero->otros_nombres." ".$registro->tercero->apellido1." ".$registro->tercero->apellido2 ]
                    ];

        $archivo_js = app($modelo->name_space)->archivo_js;

        return view('layouts.edit',compact('form_create','miga_pan','registro','archivo_js','url_action'));
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
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);


        /*
            No se stá actualizando el campo imagen, pues está en la tabla tercero
        */

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) {
            if ( $lista_campos[$i]['editable'] == 1 ) 
            { 
                    // Se valida solo si el campo pertenece al Modelo directamente
                    if ( in_array( $lista_campos[$i]['name'], $registro->getFillable() )  ) 
                    {
                        if ($lista_campos[$i]['requerido']) 
                        {
                            $this->validate($request,[$lista_campos[$i]['name']=>'required']);
                        }
                        if ($lista_campos[$i]['unico']) 
                        {
                            $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$registro->getTable().','.$lista_campos[$i]['name'].','.$id]);
                        }
                    }
            }
        }

        $registro->fill( $request->all() );
        $registro->save();


        // Actualizar datos del Tercero
        $registro->tercero->fill( $request->all() );
        $registro->tercero->save();

        return redirect('consultorio_medico/profesionales/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo)->with('flash_message','Registro MODIFICADO correctamente.');
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
}
