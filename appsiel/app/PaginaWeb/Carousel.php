<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use Storage;
use View;

use App\Sistema\Modelo;

class Carousel extends Model
{
    protected $table = 'pw_mod_carousels';

    protected $fillable = ['imagenes', 'altura_maxima', 'descripcion', 'activar_controles_laterales', 'estado'];

    public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];

    public static function consultar_registros()
    {
        $registros = Carousel::select('pw_mod_carousels.descripcion AS campo1', 'pw_mod_carousels.estado AS campo2', 'pw_mod_carousels.id AS campo3')
        ->get()
        ->toArray();
        return $registros;
    }
    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/pagina_web/modulos/pw_carousel.js';


    // Datos para Mostrar carrusel
    public static function get_array_datos( $carousel_id )
    {
        $carousel = Carousel::find( $carousel_id );
        $modelo = Modelo::where( 'modelo','pw_mod_carousels' )->get()->first();

        // Se crea un array con los datos a enviar a la vista del Módulo Carousel
        $datos = [
                      'id' => $carousel->id,
                      'descripcion' => $carousel->descripcion,
                      'altura_maxima' => $carousel->altura_maxima,
                      'activar_controles_laterales' => $carousel->activar_controles_laterales,
                      'estado' => $carousel->estado,
                      'imagenes' => []
                    ];

        // El campo "imagenes" es una cadena en formato JSON
        $i = 0;
        foreach ( json_decode( $carousel->imagenes ) as $imagen) 
        {
            // La variable $imagen es un objeto

            // Se obtiene la url de la ubicación de la imagen
            $url = config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_imagen.$imagen->imagen.'?'.rand(1,1000);

            // Se convierte el objeto a un array
            $vec_imagen = get_object_vars($imagen);
            
            // Se cambia el nombre de la imagen por la url 
            $vec_imagen['imagen'] = $url;

            $datos['imagenes'][$i] = $vec_imagen;

            $i++;
        }

        return $datos;
    }



    public static function opciones_campo_select()
    {
        $opciones = Carousel::where('estado','Activo')
                    ->select('id','descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->codigo.' '.$opcion->descripcion;
        }

        return $vec;
    }


    public static function get_url_primera_imagen( $carousel_id )
    {
        $carousel = Carousel::find( $carousel_id );
        $modelo = Modelo::where( 'modelo','pw_mod_carousels' )->get()->first();
        $imagenes = json_decode( $carousel->imagenes );
        return config('configuracion.url_instancia_cliente')."/storage/app/".$modelo->ruta_storage_imagen.$imagenes[0]->imagen.'?'.rand(1,1000);
    }
}
