<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

use App\PaginaWeb\PaginaTieneSeccion;
use App\PaginaWeb\Pagina;

class MenuItem extends Model
{
    protected $table = 'pw_menu_items';
	
    // El campo slug_id puede ser de un Articulo o una Seccion
    protected $fillable = [ 'menu_id', 'item_padre_id', 'orden', 'descripcion', 'tipo_enlace', 'pagina_id', 'slug_id', 'url_externa', 'target', 'estado'];

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/pagina_web/items_menu.js';

	public $encabezado_tabla = ['Menú', 'Descripción', 'Enlace', 'Estado', 'Acción'];
    
    public static function consultar_registros()
    {       
        return MenuItem::leftJoin('pw_menus','pw_menus.id','=','pw_menu_items.menu_id')
                        ->leftJoin('pw_slugs','pw_slugs.id','=','pw_menu_items.slug_id')
                        ->select(
                                    'pw_menus.descripcion AS campo1',
                                    'pw_menu_items.descripcion AS campo2',
                                    'pw_slugs.slug AS campo3',
                                    'pw_menu_items.estado AS campo4',
                                    'pw_menu_items.id AS campo5')
                        ->get()
                        ->toArray();
    }

    
    public static function get_items_menu( $menu_id )
    {    
        return MenuItem::leftJoin('pw_menus','pw_menus.id','=','pw_menu_items.menu_id')
                        ->leftJoin('pw_slugs','pw_slugs.id','=','pw_menu_items.slug_id')
                        ->select(
                                    'pw_menu_items.descripcion AS item_descripcion',
                                    'pw_menu_items.estado',
                                    'pw_menu_items.id',
                                    'pw_menu_items.item_padre_id',
                                    'pw_menu_items.menu_id',
                                    'pw_menu_items.tipo_enlace',
                                    'pw_menu_items.target',
                                    'pw_menu_items.orden',
                                    'pw_menu_items.slug_id',
                                    'pw_menus.descripcion AS menu_descripcion',
                                    'pw_slugs.slug',
                                    'pw_slugs.name_space_modelo')
                        ->orderBy('pw_menu_items.orden')
                        ->get();
    }

    public static function opciones_campo_select()
    {
        $opciones = MenuItem::select('id','descripcion')
                                ->get();
                    
        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    // PADRE = Página, HIJO = Sección
    public static function get_registros_select_hijo( $id_select_padre )
    {
        $registros = PaginaTieneSeccion::get_secciones_hijas_pagina( $id_select_padre );

        $opciones = '<option value="">Seleccionar...</option>';
        
        foreach ($registros as $campo)
        {
            $slug = Seccion::get_slug( $campo->id );
            $opciones .= '<option value="'.$slug->slug_id.'a3p0'.$slug->slug.'">'.$campo->titulo.'</option>';
        }

        return $opciones;
    }


    public static function get_campos_adicionales_edit( $lista_campos, $registro )
    {
        $datos_relacionados_slug = Slug::get_datos_relacionados( $registro->slug_id );
        $seccion_id = '';
        $pw_articulo_id = '';

        // Personalizar campos ( Estos no se guardan en la tabla del modelo )
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'slug':
                    $lista_campos[$i]['value'] = Slug::find( $registro->slug_id )->slug;
                    break;

                case 'seccion_id':

                    if( $datos_relacionados_slug->name_space_modelo == 'App\PaginaWeb\Seccion' )
                    {
                        $seccion_id = $datos_relacionados_slug->id;
                    }

                    $lista_campos[$i]['value'] = $seccion_id;
                    
                    break;

                case 'pw_articulo_id':

                    if( $datos_relacionados_slug->name_space_modelo == 'App\PaginaWeb\Articulo' )
                    {
                        $pw_articulo_id = $datos_relacionados_slug->id;
                    }
                    $lista_campos[$i]['value'] = $pw_articulo_id;
                    break;

                default:
                    # code...
                break;
            }
        }

        return $lista_campos;
    }



    public static function show_adicional( $lista_campos, $registro )
    {
        $slug = Slug::get_datos_relacionados( $registro->slug_id );
        $seccion_id = '';
        $pw_articulo_id = '';

        // Personalizar campos ( Estos no se guardan en la tabla del modelo )
        $cantida_campos = count($lista_campos);
        for ($i=0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'slug':
                    $lista_campos[$i]['value'] = Slug::find( $registro->slug_id )->slug;
                    break;

                case 'seccion_id':

                    if( $slug->name_space_modelo == 'App\PaginaWeb\Seccion' )
                    {
                        $seccion_id = $slug->titulo;
                    }

                    $lista_campos[$i]['value'] = $seccion_id;
                    
                    break;

                case 'pw_articulo_id':

                    if( $slug->name_space_modelo == 'App\PaginaWeb\Articulo' )
                    {
                        $pw_articulo_id = $slug->titulo;
                    }
                    $lista_campos[$i]['value'] = $pw_articulo_id;
                    break;

                default:
                    # code...
                break;
            }
        }

        return $lista_campos;
    }

}
