<?php

namespace App\PaginaWeb\Modulos;

use View;
use DB;

class Imagen
{
    // Variables que se enviarán a la vista del módulo
    public $src = ''; 
    public $clase_html = '';
    public $estilo_css = '';


    // Parámentros con las variables del módulo que se usarán para generar los campos en las acciones del CRUD
    public $parametros = '{"0":{"name":"imagen", "descripcion":"Imágen","tipo":"imagen","opciones":"","value":"null","atributos":"","definicion":"","requerido":1,"editable":1,"unico":0},"1":{"name":"clase_html","descripcion":"Clase Html","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"2":{"name":"estilo_css","descripcion":"Estilo CSS","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0}}';

    // Ubicación archivo index de la vista principal del módulo
    public $ruta_ubicacion = 'pagina_web.front_end.modulos.imagen';


    // Asignar variables al módulo según los datos almacenados en la base de datos
    public function asignar_parametros( $parametros_bd )
    {
    	$parametros = json_decode( $parametros_bd, true );

    	// Las imágenes de los módulos siempre están en la misma ubicación
    	$url_imagen = asset( config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/modulos/'.$parametros['imagen']);

	    $this->src = $url_imagen;
	    $this->clase_html = $parametros['clase_html'];
	    $this->estilo_css = $parametros['estilo_css'];
    }


    // Crea el codigo HTML del módulo según los parámetros dados
    public function dibujar_modulo( )
    {
    	return View::make( $this->ruta_ubicacion.'.index', [ 
    														'src' => $this->src, 
    														'clase_html' => $this->clase_html,
    														'estilo_css' => $this->estilo_css
    													]
    					);
    }
}
