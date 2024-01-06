<?php

namespace App\Inventarios\Recetas;

use App\Inventarios\InvProducto;

class Platillo extends InvProducto
{
    protected $table = 'inv_productos'; 

    // tipo = { producto | servicio }
    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por', 'detalle'];

    public static function opciones_campo_select()
    {
        $opciones = InvProducto::where('estado','Activo')
                            ->get();
    
        $vec['']='';
        foreach ($opciones as $opcion){
            
            if( !empty($opcion->ingredientes()) )
            {
                continue;
            }

            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
        }

        return $vec;
    }
}
