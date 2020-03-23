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
        //$nuevo_nombre = uniqid().'.'.$extension;
        $nuevo_nombre = str_slug( $archivo->getClientOriginalName() ) . '-' . uniqid() . '.' . $extension;

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

    public function carga_imagen_ckeditor()
    {
        if(isset($_FILES['upload']['name']))
        {
            $file = $_FILES['upload']['tmp_name'];
            $file_name = $_FILES['upload']['name'];
            $file_name_array = explode(".", $file_name);
            $extension = end($file_name_array);
            $new_image_name = rand() . '.' . $extension;
            //chmod('upload', 0777);
            $allowed_extension = array("jpg", "gif", "png");

            if(in_array($extension, $allowed_extension))
            {
                $ruta = asset('img/ckeditor/');
                
                move_uploaded_file($file, $ruta . $new_image_name);
                $function_number = $_GET['CKEditorFuncNum'];
                $url = $ruta . $new_image_name;
                $message = '';
                echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($function_number, '$url', '$message');</script>";
            }
        }
    }
}
