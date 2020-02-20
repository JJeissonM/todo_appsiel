<?php

namespace App\PaginaWeb\Modulos;

use View;
use DB;

class BotonVentanaModal
{
    // Variables que se enviarán a la vista del módulo
    public $id_html = ''; // como_llegar
    public $clase_html = '';
    public $estilo_css = ''; // background-color: transparent; color: #3394FF; border: none;
    public $fa_icon = ''; // map-o
    public $texto_boton = ''; // ¿Cómo llegar?
    public $titulo_modal = ''; // 'Ubicación AVIPOULET'
    public $texto_mensaje_modal = ''; //
    public $contenido_modal = ''; // '<iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15693.78596773178!2d-73.2472329!3d10.4654247!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0xd8722ec2f3c785b8!2sAVIPOULET!5e0!3m2!1ses-419!2sco!4v1569893235289!5m2!1ses-419!2sco" width="100%" height="450" frameborder="0" style="border:0;" allowfullscreen=""></iframe>'




    // Parámentros con las variables del módulo que se usarán para generar los campos en las acciones del CRUD
    public $parametros = '{"0":{"name":"id_html", "descripcion":"ID Html","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"1":{"name":"clase_html","descripcion":"Clase Html","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"2":{"name":"estilo_css","descripcion":"Estilo CSS","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"3":{"name":"fa_icon","descripcion":"FA ICON","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"4":{"name":"texto_boton","descripcion":"Texto botón","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"5":{"name":"titulo_modal","descripcion":"Título ventana modal","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"6":{"name":"texto_mensaje_modal","descripcion":"Texto mensaje ventana modal","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"7":{"name":"contenido_modal","descripcion":"Contenido ventana modal","tipo":"bsTextArea","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0}}';

    // Ubicación archivo index de la vista principal del módulo
    public $ruta_ubicacion = 'pagina_web.front_end.modulos.boton_ventana_modal';


    // Asignar variables al módulo según los datos almacenados en la base de datos
    public function asignar_parametros( $parametros_bd )
    {
    	$parametros = json_decode( $parametros_bd, true );

    	// Las imágenes de los módulos siempre están en la misma ubicación
    	//$url_imagen = asset( config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/modulos/'.$parametros['imagen']);

	    $this->id_html = $parametros['id_html'];
	    $this->clase_html = $parametros['clase_html'];
	    $this->estilo_css = $parametros['estilo_css'];
	    $this->fa_icon = $parametros['fa_icon'];
	    $this->texto_boton = $parametros['texto_boton'];
	    $this->titulo_modal = $parametros['titulo_modal'];
	    $this->texto_mensaje_modal = $parametros['texto_mensaje_modal'];
	    $this->contenido_modal = $parametros['contenido_modal'];
    }


    // Crea el codigo HTML del módulo según los parámetros dados
    public function dibujar_modulo( )
    {
    	return View::make( $this->ruta_ubicacion.'.index', [ 
    														'id_html' => $this->id_html, 
    														'clase_html' => $this->clase_html,
    														'estilo_css' => $this->estilo_css,
    														'fa_icon' => $this->fa_icon,
    														'texto_boton' => $this->texto_boton,
    														'titulo_modal' => $this->titulo_modal,
    														'texto_mensaje_modal' => $this->texto_mensaje_modal,
    														'contenido_modal' => $this->contenido_modal
    													]
    					);
    }
}
