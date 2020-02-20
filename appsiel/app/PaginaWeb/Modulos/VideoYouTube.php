<?php

namespace App\PaginaWeb\Modulos;

use View;
use DB;

class VideoYouTube
{
    // Variables que se enviarán a la vista del módulo
    public $url_video; 
    public $altura = '350px';
    public $autoplay = 0;
    public $controls = 0;


    // Parámentros con las variables del módulo que se usarán para generar los campos en las acciones del CRUD
    public $parametros = '{"0":{"name":"url_video", "descripcion":"URL Video","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":1,"editable":1,"unico":0},"1":{"name":"altura","descripcion":"Altura(px)","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"2":{"name":"autoplay","descripcion":"Reproducir automáticamente","tipo":"select","opciones":{"No":"No","Si":"Si"},"value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"3":{"name":"controls","descripcion":"Mostrar controles","tipo":"select","opciones":{"No":"No","Si":"Si"},"value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0}}';

    // Ubicación archivo index de la vista principal del módulo
    public $ruta_ubicacion = 'web.front_end.modulos.video_youtube';


    // Asignar variables al módulo según los datos almacenados en la base de datos
    public function asignar_parametros( $parametros_bd )
    {
    	$parametros = json_decode( $parametros_bd, true );

	    $this->url_video = $parametros['url_video'];
	    $this->altura = $parametros['altura'];
        $this->autoplay = $parametros['autoplay'];
        $this->controls = $parametros['controls'];
    }


    // Crea el codigo HTML del módulo según los parámetros dados
    public function dibujar_modulo( )
    {
    	return View::make( $this->ruta_ubicacion.'.index', [ 
    														'url_video' => $this->url_video, 
    														'altura' => $this->altura,
                                                            'autoplay' => $this->autoplay,
                                                            'controls' => $this->controls
    													]
    					);
    }
}
