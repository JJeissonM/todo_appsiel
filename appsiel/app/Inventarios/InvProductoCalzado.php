<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;


class InvProductoCalzado extends Model
{
    protected $table = 'inv_productos'; 

    /*
        unidad_medida2 = Talla
    */
    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = ['Código', 'Grupo inventario', 'Descripción', 'Talla', 'Referencia', 'Estado', 'Acción'];


    // unidad_medida2 = Talla
    public static function consultar_registros()
    {
        $array_wheres = [ 
                            [ 'inv_productos.core_empresa_id', Auth::user()->empresa_id]
                        ];

        return InvProductoCalzado::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->where( $array_wheres )
                    ->select(
                                'inv_productos.id AS campo1',
                                'inv_grupos.descripcion AS campo2',
                                'inv_productos.descripcion AS campo3',
                                'inv_productos.unidad_medida2 AS campo4',
                                'inv_productos.referencia AS campo5',
                                'inv_productos.estado AS campo6',
                                'inv_productos.id AS campo7')
                    ->get()
                    ->toArray();
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvProductoCalzado::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
