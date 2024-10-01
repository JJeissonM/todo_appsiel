<?php

namespace App\Http\Controllers\PaginaWeb;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\ImagenController;

use App\Sistema\Modelo;
use App\PaginaWeb\TipoModulo;
use App\PaginaWeb\Modulo;
use App\Sistema\Services\ModeloService;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;

class ModuloController extends Controller
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
        $registros = TipoModulo::where('estado', 'Activo')->get();

        $tipos_modulos['']='';
        foreach ($registros as $opcion){
            $tipos_modulos[$opcion->id] = $opcion->descripcion;
        }

        $miga_pan = [
                        ['url'=>'pagina_web?id='.Input::get('id'),'etiqueta'=>'Página Web'],
                        ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Módulos'],
                        ['url'=>'NO','etiqueta'=>'Nuevo']
                    ];

        return view( 'pagina_web.back_end.modulos.create', compact( 'miga_pan','tipos_modulos' ) );
    }

    /**
     * Formulario para crear nuevo Módulo según el tipo escogido
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function crear_nuevo(Request $request)
    {
        $tipo_modulo = TipoModulo::find($request->tipo_modulo);

        $modelo = Modelo::find($request->url_id_modelo);

        $modelo_service = new ModeloService();

        // Se obtienen los campos que tiene ese modelo (Model Modulo)
        $lista_campos = $modelo_service->get_campos_modelo($modelo, '', 'create');
        
        // Obtener campos adicionales (parámetros) según el Tipo Modulo
        $parametros = [];
        if ( $tipo_modulo->modelo != '') 
        {
            $obj = new $tipo_modulo->modelo;
            
            $parametros = $modelo_service->ajustar_valores_lista_campos( json_decode( $obj->parametros, true ) );
        }

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => array_merge($lista_campos,$parametros)
                    ];

        $miga_pan = [
                        ['url'=>'pagina_web?id='.$request->url_id,'etiqueta'=>'Página Web'],
                        ['url'=>'web?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo,'etiqueta'=>'Módulos'],
                        ['url'=>'NO','etiqueta'=>'Nuevo: '.$tipo_modulo->descripcion]
                    ];

        $archivo_js = app($modelo->name_space)->archivo_js;

        $url_action = 'web';
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        return view( 'layouts.create', compact( 'miga_pan','form_create', 'tipo_modulo','url_action', 'archivo_js' ) );
    }

    /**
     * Almacenar el campo parámetros.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $crud = new ModeloController;
        $registro = $crud->crear_nuevo_registro( $request );

        // Las imágenes de los módulos siempre están en la misma ubicación
        $ruta_storage_imagen = 'pagina_web/modulos/';

        $tipo_modulo = TipoModulo::find($request->tipo_modulo);
        
        $parametros = [];
        // Obtener campos adicionales (parámetros) según el Tipo Modulo
        if ( $tipo_modulo->modelo != '') 
        {

            // Nuevo objeto del tipo de módulo
            $obj = new $tipo_modulo->modelo;
            $parametros_vacios = json_decode( $obj->parametros, true );

            $cant = count($parametros_vacios);

            // Se recorren los parámetro del tipo de módulo para crear una cadena tipo JSON con los campos enviados en el $request
            $parametros_a_guardar = '{';
            $primero = true;
            for($i=0;$i<$cant;$i++) {
                // 
                $nombre_campo = $parametros_vacios[$i]['name'];
                $valor = $request->$nombre_campo;

                // Para Imágen individual
                if ( $nombre_campo == 'imagen')
                {
                    //dd( $request->file() );
                    // NOTA: $request->imagen debe ser un Input tipo File
                    $valor = ImagenController::guardar_imagen_en_disco( $request->imagen, $ruta_storage_imagen );
                }

                if( $primero ) {
                    $parametros_a_guardar .= '"'.$nombre_campo.'":"'.$valor.'"';
                    $primero = false;
                }else{
                    $parametros_a_guardar .= ',"'.$nombre_campo.'":"'.$valor.'"';
                }            
            }
            $parametros_a_guardar .= '}';

            $registro->parametros = $parametros_a_guardar;
            $registro->save();
        }

        return redirect( 'web'.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );

        //return redirect( 'web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        echo "Vista pendiente.";
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $registro = Modulo::find( $id );

        $tipo_modulo = TipoModulo::find($registro->tipo_modulo);

        $modelo = Modelo::find( Input::get('id_modelo') );

        $modelo_service = new ModeloService();

        // Se obtienen los campos que tiene ese modelo (Model Modulo)
        $lista_campos = $modelo_service->get_campos_modelo($modelo,'','create');
        
        // Obtener campos adicionales (parámetros) según el Tipo Modulo
        $parametros_vacios = [];
        if ( $tipo_modulo->modelo != '') 
        {
            $obj = new $tipo_modulo->modelo;
            $parametros_vacios = json_decode( $obj->parametros, true );
            
            $parametros_vacios = $modelo_service->ajustar_valores_lista_campos( $parametros_vacios );
        
        }


        $parametros_llenos = json_decode( $registro->parametros, true ); // json_decode( $registro->parametros, true ) 

        $cant = count($parametros_vacios);
        for($i=0;$i<$cant;$i++)
        {
            
            $nombre_campo = $parametros_vacios[$i]['name'];

            $registro->$nombre_campo = '';

            if ( isset( $parametros_llenos[ $nombre_campo ] ) )
            {
                $registro->$nombre_campo = $parametros_llenos[ $nombre_campo ];
            }
            

            // Para Imágen individual
            /**/if ( $nombre_campo == 'imagen')
            {
                $parametros_vacios[$i]['value'] = config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/modulos/'.$parametros_llenos[ $nombre_campo ];
            }
         
        }

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => array_merge($lista_campos,$parametros_vacios)
                    ];

        //dd( $form_create );

        $miga_pan = [
                        ['url' => 'pagina_web?id='.Input::get('id'),'etiqueta'=>'Página Web'],
                        ['url' => 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=> $modelo->descripcion ],
                        ['url' => 'NO','etiqueta'=> $registro->descripcion.' ( Tipo: '.$tipo_modulo->descripcion.' )']
                    ];

        $archivo_js = app($modelo->name_space)->archivo_js;

        $url_action = 'pagina_web/modulos/'.$registro->id;

        return view( 'layouts.edit', compact('registro', 'miga_pan','form_create', 'tipo_modulo','url_action', 'archivo_js' ) );
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
        $crud = new ModeloController;

        $registro = Modulo::find( $id );
        $parametros_llenos = json_decode( $registro->parametros, true );

        // Las imágenes de los módulos siempre están en la misma ubicación
        $ruta_storage_imagen = 'pagina_web/modulos/';

        $tipo_modulo = TipoModulo::find($registro->tipo_modulo);
        
        $parametros = [];
        $parametros_a_guardar = '';
        // Obtener campos adicionales (parámetros) según el Tipo Modulo
        if ( $tipo_modulo->modelo != '') 
        {
            // Nuevo objeto del tipo de módulo
            $obj = new $tipo_modulo->modelo;
            $parametros_vacios = json_decode( $obj->parametros, true );

            $cant = count($parametros_vacios);

            // Se recorren los parámetro del tipo de módulo para crear una cadena tipo JSON con los campos enviados en el $request
            $parametros_a_guardar = '{';
            $primero = true;
            for($i=0;$i<$cant;$i++)
            {
                // 
                $nombre_campo = $parametros_vacios[$i]['name'];
                $valor = $request->$nombre_campo;

                // Para Imágen individual
                if ( $nombre_campo == 'imagen')
                {
                    $valor = $parametros_llenos[ $nombre_campo ];;

                    if ( $request->imagen != '')
                    {   
                        // Si se envía una nueva imagen, borrar la anterior
                        Storage::delete($ruta_storage_imagen.$valor);
                        // NOTA: $request->imagen debe ser un Input tipo File
                        $valor = ImagenController::guardar_imagen_en_disco( $request->imagen, $ruta_storage_imagen );
                    }
                }

                if( $primero ) {
                    $parametros_a_guardar .= '"'.$nombre_campo.'":"'.$valor.'"';
                    $primero = false;
                }else{
                    $parametros_a_guardar .= ',"'.$nombre_campo.'":"'.$valor.'"';
                }            
            }
            $parametros_a_guardar .= '}';
        }

        $registro->fill( array_merge( $request->all(), ['parametros' => $parametros_a_guardar] ) );
        $registro->save();

        return redirect( 'web'.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro MODIFICADO correctamente.' );
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
