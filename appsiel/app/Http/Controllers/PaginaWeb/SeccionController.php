<?php

namespace App\Http\Controllers\PaginaWeb;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Storage;
use View;
use Input;

use App\Sistema\Modelo;

use App\PaginaWeb\Seccion;

class SeccionController extends ModeloController
{



    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        echo "hola";
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Si tiene una accion diferente para el envío del formulario
        $url_action = 'web';
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        $miga_pan = $this->get_miga_pan($modelo,'Crear nuevo');

        return view( 'pagina_web.secciones.create', compact('url_action', 'miga_pan') );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $seccion = Modelo::find($request->url_id_modelo);

        //$core_modelo = new ModeloController;
        //$core_modelo->validar_requeridos_y_unicos($request, $seccion);

        // Crear el nuevo registro
        $registro = app($seccion->name_space)->create( $request->all() );

        return redirect( 'web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Si tiene una accion diferente para el envío del formulario
        $url_action = 'web';
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }

        $registro = app($modelo->name_space)->where('id',$id)->get()->first();

        $miga_pan = $this->get_miga_pan($modelo,$registro->descripcion);

        return view( 'pagina_web.secciones.edit', compact('registro', 'url_action', 'miga_pan') );
    }

}
