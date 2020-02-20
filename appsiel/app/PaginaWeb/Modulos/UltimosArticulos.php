<?php

namespace App\PaginaWeb\Modulos;

use View;
use DB;

use App\PaginaWeb\Articulo;

class UltimosArticulos
{
	// Este móduo muestra los últimos artículos creados en un formato de panel BootStrap 3
	// Si se escoge una categoría, solo muestra los útimos creados de esa categoría

    // Variables que se enviarán a la vista del módulo
    public $cantidad_a_mostrar;
    public $categoria_id;
    public $cant_cols;
    public $mostrar_titulo; // Indica si se muestra el header del panel
    public $mostrar_resumen;
    public $mostrar_imagen;
    public $altura_imagen;

    protected $articulos;


    // Parámentros con las variables del módulo y que se usarán para generar los campos en las acciones del CRUD para el ModuloController
    public $parametros = '{"0":{"name":"cantidad_a_mostrar","descripcion":"No. de artículos a mostar","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":1,"editable":1,"unico":0},"1":{"name":"categoria_id", "descripcion":"Categoría","tipo":"select","opciones":"model_App\\\PaginaWeb\\\Categoria","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"2":{"name":"cant_cols","descripcion":"Cantidad de columnas","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":1,"editable":1,"unico":0},"3":{"name":"mostrar_titulo","descripcion":"Mostrar títulos de artículos","tipo":"select","opciones":{"1":"Si","0":"No"},"value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"4":{"name":"mostrar_resumen","descripcion":"Mostrar resúmenes de artículos","tipo":"select","opciones":{"1":"Si","0":"No"},"value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"5":{"name":"mostrar_imagen","descripcion":"Mostrar imágenes de artículos","tipo":"select","opciones":{"1":"Si","0":"No"},"value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"6":{"name":"altura_imagen","descripcion":"Altura imagen (px)","tipo":"bsText","opciones":"","value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0}}';

    // Ubicación archivo index de la vista principal del módulo
    public $ruta_ubicacion = 'pagina_web.front_end.modulos.ultimos_articulos';


    // Asignar variables al módulo según los datos almacenados en la base de datos
    public function asignar_parametros( $parametros_bd )
    {
    	$parametros = json_decode( $parametros_bd, true );

	    //$this->cantidad_a_mostrar = $parametros['cantidad_a_mostrar'];
    	//$this->categoria_id = $parametros['categoria_id'];
	    $this->cant_cols = $parametros['cant_cols'];
	    $this->mostrar_titulo = $parametros['mostrar_titulo'];
	    $this->mostrar_resumen = $parametros['mostrar_resumen'];
	    $this->mostrar_imagen = $parametros['mostrar_imagen'];
	    $this->altura_imagen = $parametros['altura_imagen'];

	    //
	    $cant_articulos = 999;

	    if( $parametros['cantidad_a_mostrar'] != '' )
	    {
	    	$cant_articulos = $parametros['cantidad_a_mostrar'];
	    }

	    // Validaciones de categoría
	    $operador = 'LIKE';
	    $id_categoria = '%%';

	    if( $parametros['categoria_id'] != '' )
	    {
	    	$operador = '=';
	    	$id_categoria = $parametros['categoria_id'];
	    }

	    //dd( $operador );

    	$this->articulos = Articulo::where('estado','Activo')->where('categoria_id',$operador,$id_categoria)->orderBy('updated_at', 'desc')->get()->take($cant_articulos);
    }

    // 
    public function dibujar_modulo( )
    {
    	return View::make($this->ruta_ubicacion.'.index', [ 'cant_cols' => $this->cant_cols, 'mostrar_titulo' => $this->mostrar_titulo, 'mostrar_resumen' => $this->mostrar_resumen, 'mostrar_imagen' => $this->mostrar_imagen, 'altura_imagen' => $this->altura_imagen, 'articulos' => $this->articulos]);
    }

}