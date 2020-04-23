<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

/*
        -------------------  OJO ----------------
    CORREGIR PARA LOS CLIENTES NO LOGUEADOS EN LA WEB
    SE COMENTÓ LA LÍNEA DE PEDIR AUTENCIACIÓN
*/
use Auth;


use App\Inventarios\InvGrupo;

use App\Contabilidad\Impuesto;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;

class InvProducto extends Model
{
    //protected $table = 'inv_productos'; 

    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por'];

    public $encabezado_tabla = ['Código','Descripción','UM-1', 'Grupo inventario','Precio compra','Precio venta','IVA','Tipo','Estado','Acción'];

    public static function consultar_registros()
    {
        $registros = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
                    ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
                    ->select('inv_productos.id AS campo1','inv_productos.descripcion AS campo2','inv_productos.unidad_medida1 AS campo3','inv_grupos.descripcion AS campo4','inv_productos.precio_compra AS campo5','inv_productos.precio_venta AS campo6','contab_impuestos.tasa_impuesto AS campo7','inv_productos.tipo AS campo8','inv_productos.estado AS campo9','inv_productos.id AS campo10')
                    ->get()
                    ->toArray();

        return $registros;
    }


    public static function get_datos_basicos($grupo_inventario_id, $estado)
    {

        if ( $grupo_inventario_id == '')
        {
          $grupo_inventario_id = '%'.$grupo_inventario_id.'%';
          $operador1 = 'LIKE';
        }else{
          $operador1 = '=';
        }

        return InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
                    ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
                    ->where('inv_productos.inv_grupo_id', $operador1, $grupo_inventario_id)
                    ->where('inv_productos.estado', $estado)
                    ->select(
                                'inv_productos.id',
                                'inv_productos.descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_grupos.descripcion AS grupo_descripcion',
                                'inv_productos.precio_compra',
                                'inv_productos.precio_venta',
                                'contab_impuestos.tasa_impuesto',
                                'inv_productos.tipo',
                                'inv_productos.estado',
                                'inv_productos.imagen',
                                'inv_productos.mostrar_en_pagina_web',
                                'inv_productos.codigo_barras')
                    ->get();
    }


    public static function get_datos_pagina_web($grupo_inventario_id, $estado)
    {
        if ( $grupo_inventario_id == '')
        {
          $grupo_inventario_id = '%'.$grupo_inventario_id.'%';
          $operador1 = 'LIKE';
        }else{
          $operador1 = '=';
        }

        $productos = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
                    //->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
                    ->where('inv_productos.inv_grupo_id', $operador1, $grupo_inventario_id)
                    ->where('inv_productos.estado', $estado)
                    ->where('inv_productos.mostrar_en_pagina_web', 1)
                    ->select(
                                'inv_productos.id',
                                'inv_productos.descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_grupos.descripcion AS grupo_descripcion',
                                'inv_productos.precio_compra',
                                'inv_productos.precio_venta',
                                'contab_impuestos.tasa_impuesto',
                                'inv_productos.tipo',
                                'inv_productos.estado',
                                'inv_productos.imagen',
                                'inv_productos.mostrar_en_pagina_web',
                                'inv_productos.codigo_barras')
                    ->orderBy('grupo_descripcion')
                    ->get();
                    
        foreach ($productos as $item)
        {
            $item->precio_venta = ListaPrecioDetalle::get_precio_producto( config('pagina_web.lista_precios_id'), date('Y-m-d'), $item->id );

            $item->descuento = ListaDctoDetalle::get_descuento_producto( config('pagina_web.lista_descuentos_id'), date('Y-m-d'), $item->id );
        }

        return $productos;
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvProducto::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }
    

    public static function get_cuenta_inventarios( $producto_id )
    {
        $inv_grupo_id = InvProducto::where( 'id', $producto_id )->value( 'inv_grupo_id' );
        return InvGrupo::where( 'id', $inv_grupo_id )->value( 'cta_inventarios_id' );
    }

    // get_cuenta_ingresos
    public static function get_cuenta_ingresos( $producto_id )
    {
        $inv_grupo_id = InvProducto::where( 'id', $producto_id )->value( 'inv_grupo_id' );
        return InvGrupo::where( 'id', $inv_grupo_id )->value( 'cta_ingresos_id' );
    }

    // get_cuenta_impuesto_compras
    public static function get_cuenta_impuesto_compras( $producto_id )
    {
        $impuesto_id = InvProducto::where( 'id', $producto_id )->value( 'impuesto_id' );
        return Impuesto::where( 'id', $impuesto_id )->value( 'cta_compras_id' );
    }

    // get_cuenta_impuesto_devolucion_compras
    public static function get_cuenta_impuesto_devolucion_compras( $producto_id )
    {
        $impuesto_id = InvProducto::where( 'id', $producto_id )->value( 'impuesto_id' );
        return Impuesto::where( 'id', $impuesto_id )->value( 'cta_compras_devol_id' );
    }

    // get_cuenta_impuesto_ventas
    public static function get_cuenta_impuesto_ventas( $producto_id )
    {
        $impuesto_id = InvProducto::where( 'id', $producto_id )->value( 'impuesto_id' );
        return Impuesto::where( 'id', $impuesto_id )->value( 'cta_ventas_id' );
    }

    // get_cuenta_impuesto_devolucion_ventas
    public static function get_cuenta_impuesto_devolucion_ventas( $producto_id )
    {
        $impuesto_id = InvProducto::where( 'id', $producto_id )->value( 'impuesto_id' );
        return Impuesto::where( 'id', $impuesto_id )->value( 'cta_ventas_devol_id' );
    }


    public static function get_tasa_impuesto( $producto_id )
    {
        $impuesto_id = InvProducto::where( 'id', $producto_id )->value( 'impuesto_id' );
        
        if($impuesto_id == 0)
        {
            return 0;
        }

        return Impuesto::where( 'id', $impuesto_id )->value( 'tasa_impuesto' );
    }

}
