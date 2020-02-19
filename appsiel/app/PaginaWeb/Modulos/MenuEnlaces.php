<?php

namespace App\PaginaWeb\Modulos;

use View;
use DB;

class MenuEnlaces
{
    // Variables que se enviarán a la vista del módulo
    public $clase_fixed = ''; // navbar-fixed-top | navbar-fixed-bottom
    public $mostrar_logo = false;
    public $url_logo = '';
    public $slogan = '';
    public $alineacion_items = '';
    public $menu_id;
    protected $lista_items;


    // Parámentros con las variables del módulo y que se usarán para generar los campos en las acciones del CRUD para el ModuloController
    public $parametros = '{"0":{"name":"modo_horizontal", "descripcion":"Modo horizontal","tipo":"select","opciones":{"0":"No","1":"Si"},"value":"null","atributos":"","definicion":"","requerido":0,"editable":1,"unico":0},"1":{"name":"menu_id","descripcion":"Menú a mostrar","tipo":"select","opciones":"model_App\\\PaginaWeb\\\Menu","value":"null","atributos":"","definicion":"","requerido":1,"editable":1,"unico":0}}';

    // Ubicación archivo index de la vista principal del módulo
    public $ruta_ubicacion = 'pagina_web.front_end.modulos.menu_enlaces';


    // Asignar variables al módulo según los datos almacenados en la base de datos
    public function asignar_parametros( $parametros_bd )
    {
    	$parametros = json_decode( $parametros_bd, true );

    	// Las imágenes de los módulos siempre están en la misma ubicación
    	$url_logo = asset( config('configuracion.url_instancia_cliente').'/storage/app/pagina_web/modulos/'.$parametros['imagen']);

    	$this->clase_fixed = $parametros['clase_fixed'];
	    $this->mostrar_logo = $parametros['mostrar_logo'];
	    $this->url_logo = $url_logo;
	    $this->slogan = $parametros['slogan'];
	    $this->alineacion_items = $parametros['alineacion_items'];
	    $this->menu_id = $parametros['menu_id'];
    }

    // 
    public function dibujar_modulo( )
    {
    	$lista_items = $this->generar_items_menu();

    	return View::make($this->ruta_ubicacion.'.index', [ 'menu_id' => $this->menu_id, 'clase_fixed' => $this->clase_fixed, 'mostrar_logo' => $this->mostrar_logo, 'url_logo' => $this->url_logo, 'slogan' => $this->slogan, 'alineacion_items' => $this->alineacion_items, 'lista_items' => $lista_items]);
    }


    public function generar_items_menu()
    {
    	$data = DB::table('pw_menu_items')->where(['menu_id' => $this->menu_id, 'estado' => 'Activo'])->orderBy('orden')->get();
  		
  		// Se crea el árbol de items
		$menuAll = [];
		foreach ($data as $linea1) 
		{
			$line = (array)$linea1;
			$item = [ array_merge($line, ['submenu' => $this->getChildren($data, $line) ]) ];
			$menuAll = array_merge($menuAll, $item);
		}

		// Se crean los elementos Html de cada item
		$lista_items = '';
		foreach($menuAll as $key => $item)
        {
        	// Si es un item children, se salta porque será dibujado dentro de su item padre
			if ($item['item_padre_id'] != 0)
			{
				break;
			}
        	$lista_items .= $this->dibujar_item($item);
        }

        return $lista_items;
    }

    public function getChildren($data, $line)
	{
		$children = [];
		foreach ($data as $linea)
		{
			$line1 = (array)$linea;

			if ($line['id'] == $line1['item_padre_id'])
			{
				$children = array_merge($children, [ array_merge($line1, ['submenu' => $this->getChildren($data, $line1) ]) ]);
			}
		}
		return $children;
	}

	public function dibujar_item($item)
	{	
		$html_item = '';
		if($item['submenu'] == [])
		{
		    $html_item .= '<li><a href="'.url( $item['enlace'] ) .'" target="'.$item['target'].'">'.$item['descripcion'].'</a></li>';
		}else{
		    $html_item .= '<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$item['descripcion'].'<span class="caret"></span></a> <ul class="dropdown-menu sub-menu">';

		    		$html_subitem = '';
		            foreach ($item['submenu'] as $submenu)
		            {    
		            	if ($submenu['submenu'] == [])
		                {   
		                	$html_subitem .= '<li><a href="'.url( $submenu['enlace'] ).'" target="'.$item['target'].'">'.$submenu['descripcion'].' </a></li>';
		                }else{
		                    $html_subitem .= $this->dibujar_item($submenu);
		                }
		            }

		    $html_item .= $html_subitem.'</ul></li>';
		}
		return $html_item;
	}
}
