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

use App\PaginaWeb\Carousel;

class CarouselController extends ModeloController
{


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /*$modelo = Modelo::find(Input::get('id_modelo'));

        // Si tiene una accion diferente para el envío del formulario
        $url_action = 'web';
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        }


        return view( 'pagina_web.back_end.modulos.carousel.create', compact('url_action', 'miga_pan') );
        */

        return redirect('web/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&vista=pagina_web.back_end.modulos.carousel.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $general = new ModeloController;
        $modelo = Modelo::find( $request->url_id_modelo );

        $datos = [ 
                    'altura_maxima' => $request->altura_maxima, 
                    'descripcion' => $request->descripcion,
                    'activar_controles_laterales' => $request->activar_controles_laterales, 
                    'estado' => $request->estado 
                ];

        // Los archivos tipo file vienen en un array con el índice "imagenes"
        $archivos_enviados = $request->file()["imagenes"];
        $i = 0;
        foreach ($archivos_enviados as $archivo) 
        {
            $extension =  $archivo->clientExtension();

            $nuevo_nombre = uniqid().'.'.$extension;

            // El favicon se almacena en la carpeta public
            Storage::put($modelo->ruta_storage_imagen.$nuevo_nombre,
                file_get_contents( $archivo->getRealPath() ) 
                );

            // Se arma un vector con las imagenes
            $vec_imagenes[$i]['imagen'] = $nuevo_nombre;
            $vec_imagenes[$i]['texto'] = $request->input('textos_imagenes.'.$i);
            $vec_imagenes[$i]['enlace'] = $request->input('enlaces_imagenes.'.$i);
            $i++;
        }

        // Se almacena el registro con el array de imagenes tranformado en formato json
        $datos['imagenes'] = json_encode($vec_imagenes);
        $registro = Carousel::create( $datos );

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
        return redirect('web/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'));
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

        return view( 'pagina_web.back_end.modulos.carousel.edit', compact('registro', 'url_action', 'miga_pan') );
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
        $general = new ModeloController;
        $modelo = Modelo::find( $request->url_id_modelo );

        $datos = [ 
                    'altura_maxima' => $request->altura_maxima, 
                    'descripcion' => $request->descripcion,
                    'activar_controles_laterales' => $request->activar_controles_laterales,
                    'estado' => $request->estado 
                ];

        $vec_imagenes = [];

        /**
         * Se revisa si se eliminaron imagenes anteriores para borrarlas del disco
         */
        $registro = app($modelo->name_space)->where('id',$id)->get()->first();
        $imagenes_vigentes = json_decode($registro->imagenes);
        $cant_img_vigentes = count( $imagenes_vigentes );

        $imagenes_enviadas = $request->imagenes_anteriores;
        $cant_img_enviadas = count( $imagenes_enviadas );

        if ( $cant_img_vigentes != $cant_img_enviadas) {
            $borrar = true;
            // Se recorre el array de imagenes anteriores vigentes (las que estan aún en la BD y en el disco)
            for ($i=0; $i < $cant_img_vigentes; $i++) {

                // Se recorre el array de imagenes anteriores enviadas (las de ahora)
                for ($j=0; $j < $cant_img_enviadas; $j++) {

                    // Si la imagen vigente $i es igual a la imagen enviada $j ( si está en el array )
                    if ( $imagenes_vigentes[$i]->imagen == $imagenes_enviadas[$j] ) {
                        $borrar = false;
                        break;
                    }else{
                        $borrar = true;
                    }

                }

                // Borrar del disco la imagen vigente si no está en el array de las imagenes enviadas
                if ( $borrar ) {
                    Storage::delete($modelo->ruta_storage_imagen.$imagenes_vigentes[$i]->imagen);
                    //echo "borrar: ".$imagenes_vigentes[$i]->imagen."<br><br>";
                }
            }
        }

        // NOTA: La cantidad de inputs tipo file enviados van a diferir de la cantidada de inputs tipo text

        // Se continua armando el array de datos, primero con las imagenes enviadas
        for ($contador=0; $contador < $cant_img_enviadas; $contador++) {
            $vec_imagenes[$contador]['imagen'] = $imagenes_enviadas[$contador];
            $vec_imagenes[$contador]['texto'] = $request->input('textos_imagenes.'.$contador);
            $vec_imagenes[$contador]['enlace'] = $request->input('enlaces_imagenes.'.$contador);
        }

        // Se sigue utilizando el mismo $contador

        /*
         * Se recorre el array de inputs tipo file
         */
        if( !empty( $request->file() ) )
        {
            $archivos_enviados = $request->file()["imagenes"];
            foreach ($archivos_enviados as $archivo) 
            {
                $extension =  $archivo->clientExtension();

                $nuevo_nombre = uniqid().'.'.$extension;

                Storage::put($modelo->ruta_storage_imagen.$nuevo_nombre, file_get_contents( $archivo->getRealPath() ) );

                $vec_imagenes[$contador]['imagen'] = $nuevo_nombre;
                $vec_imagenes[$contador]['texto'] = $request->input('textos_imagenes.'.$contador);
                $vec_imagenes[$contador]['enlace'] = $request->input('enlaces_imagenes.'.$contador);
                $contador++;
            }
        }
            

        //dd($vec_imagenes);

        // Se almacena el registro con el array de imagenes tranformado en formato json
        $datos['imagenes'] = json_encode($vec_imagenes);
        $registro->fill( $datos );
        $registro->save( );

        return redirect( 'web/'.$registro->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro MODIFICADO correctamente.' );
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
