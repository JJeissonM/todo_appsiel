<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use Storage;

use App\Sistema\Modelo;

class ImagenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Recibe un Input tipo File y la Ruta de la imagen
    // Devuelve el nombre del archivo creado
    public static function guardar_imagen_en_disco( $archivo, $ruta_storage_imagen )
    {
        $extension =  $archivo->clientExtension();

        // Crear un nombre unico para el archivo con su misma extensión
        $nuevo_nombre = uniqid().'.'.$extension;

        // Guardar la imagen en disco
        Storage::put( $ruta_storage_imagen.$nuevo_nombre, file_get_contents( $archivo->getRealPath() ) );

        return $nuevo_nombre;
    }

    public function quitar_imagen()
    {
    	// Se obtiene la instacia del modelo por su ID
    	$modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro del modelo indicado
        $registro = app($modelo->name_space)->find( Input::get('registro_id') );

        // Borrar imágen del disco
        Storage::delete($modelo->ruta_storage_imagen.$registro->imagen);

        // Borrar imágen de la BD
        $registro->imagen = "";
        $registro->save();

        return redirect('web/'.Input::get('registro_id').'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'))->with('flash_message','Imágen eliminada correctamente.');
    } 
}
