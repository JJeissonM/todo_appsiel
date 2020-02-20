<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class ArticuloItemMenu extends Articulo
{
    protected $table = 'pw_articulos';

    public static function opciones_campo_select()
    {
        $opciones = Articulo::leftJoin('pw_slugs','pw_slugs.id','=','pw_articulos.slug_id')
                                ->where('pw_articulos.estado','=','Activo')
                                ->select( 'pw_slugs.slug', 'pw_slugs.id AS slug_id', 'pw_articulos.titulo')
                                ->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->slug_id.'a3p0'.$opcion->slug] = $opcion->titulo;
        }
        
        return $vec;
    }
}
