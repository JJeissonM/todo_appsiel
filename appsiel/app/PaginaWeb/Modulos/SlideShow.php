<?php

namespace App\PaginaWeb\Modulos;

use View;
use DB;

use App\PaginaWeb\Carousel;

class SlideShow
{
    // Variables que se enviarán a la vista del módulo
    public $datos; 

    //public $tabla_ingreso_imagenes = '<table class="table table-bordered" id="ingreso_registros"><thead><tr><th> Imágen </th><th> Texto informativo </th><th> Enlace </th><th> Acción </th></tr></thead><tbody></tbody></table><p>El peso máximo permitido para cada imágen es de <mark>2 MB</mark></p><button type="button" style="background-color: transparent; color: #3394FF; border: none;" id="btn_nueva_linea"><i class="fa fa-btn fa-plus"></i> Agregar imágen </button>';

    // Parámentros con las variables del módulo y que se usarán para generar los campos en las acciones del CRUD para el ModuloController
    public $parametros = '{"0":{"name":"carousel_id","descripcion":"Albúm","tipo":"select","opciones":"model_App\\\PaginaWeb\\\Carousel","value":"null","atributos":"","definicion":"","requerido":1,"editable":1,"unico":0}}';

    // Ubicación archivo index de la vista principal del módulo
    public $ruta_ubicacion = 'web.front_end.modulos.carousel';


    // Asignar variables al módulo según los datos almacenados en la base de datos
    public function asignar_parametros( $parametros_bd )
    {
    	$parametros = json_decode( $parametros_bd, true );

	    // Se crea un array con los datos a enviar a la vista del Módulo Carousel
        $this->datos = Carousel::get_array_datos( $parametros['carousel_id'] );

    }

    // 
    public function dibujar_modulo( )
    {    	
    	return View::make($this->ruta_ubicacion.'.index', [ 'datos' => $this->datos ]);
    }
}
