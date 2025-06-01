<?php

namespace App\Inventarios;

use Illuminate\Support\Facades\Auth;

class ItemSinEnsambleAutomatico extends InvProducto
{
    protected $table = 'inv_productos'; 
    
    public static function opciones_campo_select()
    {
        
        $opciones = InvProducto::leftJoin('inv_items_desarmes_automaticos','inv_items_desarmes_automaticos.item_producir_id','=','inv_productos.id')
                            ->whereNull('inv_items_desarmes_automaticos.item_producir_id')
                            ->where('inv_productos.estado','Activo')
                            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
                            ->select( 'inv_productos.id', 'inv_productos.descripcion' )
                            ->get();

        if( gettype( strpos( url()->full(), 'edit' ) ) == 'integer' )
        {
            $opciones = InvProducto::leftJoin('inv_items_desarmes_automaticos','inv_items_desarmes_automaticos.item_producir_id','=','inv_productos.id')
                            ->where('inv_productos.estado','Activo')
                            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
                            ->select( 'inv_productos.id', 'inv_productos.descripcion' )
                            ->get();
        }

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
