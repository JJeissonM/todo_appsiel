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

    // tipo = { producto | servicio }
    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por', 'detalle'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código',  'Referencia', 'Descripción', 'U.M.', 'Grupo inventario', 'IVA', 'Tipo', 'Mostrar en Página Web', 'Estado'];

    public function grupo_inventario()
    {
        return $this->belongsTo(InvGrupo::class, 'inv_grupo_id');
    }

    public function impuesto()
    {
        return $this->belongsTo('App\Contabilidad\Impuesto', 'impuesto_id');
    }

    public function fichas()
    {
        return $this->hasMany(InvFichaProducto::class, 'producto_id', 'id');
    }

    public function get_productos($tipo)
    {
        $opciones = InvProducto::where('estado', 'Activo')
            ->where('tipo', 'LIKE', '%' . $tipo . '%')
            ->get();
        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection =  InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where( 'inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_productos.id AS campo1',
                'inv_productos.referencia AS campo2',
                'inv_productos.descripcion AS campo3',
                'inv_productos.unidad_medida1 AS campo4',
                'inv_grupos.descripcion AS campo5',
                'contab_impuestos.tasa_impuesto AS campo6',
                'inv_productos.tipo AS campo7',
                'inv_productos.mostrar_en_pagina_web AS campo8',
                'inv_productos.estado AS campo9',
                'inv_productos.id AS campo10'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida1", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_compra", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_venta", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("inv_productos.tipo", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->paginate($nro_registros);

        if (count($collection) > 0)
        {
            foreach ($collection as $c)
            {
                if ( $c->campo8 )
                {
                    $c->campo8 = 'Si';
                }else{
                    $c->campo8 = 'No';
                }

                $c->campo6 = $c->campo6 . '%';
            }
        }
        
        return $collection;
    }

    /*public static function consultar_registros($nro_registros, $search)
    {
        dd( Auth::user()->empresa_id );

        $collection =  InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where( 'inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_productos.id AS campo1',
                'inv_productos.referencia AS campo2',
                'inv_productos.descripcion AS campo3',
                'inv_productos.unidad_medida1 AS campo4',
                'inv_grupos.descripcion AS campo5',
                'contab_impuestos.tasa_impuesto AS campo6',
                'inv_productos.tipo AS campo7',
                'inv_productos.mostrar_en_pagina_web AS campo8',
                'inv_productos.estado AS campo9',
                'inv_productos.id AS campo10'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida1", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_compra", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_venta", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("inv_productos.tipo", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->paginate($nro_registros);

        if (count($collection) > 0)
        {
            foreach ($collection as $c)
            {
                if ( $c->campo8 )
                {
                    $c->campo8 = 'Si';
                }else{
                    $c->campo8 = 'No';
                }

                $c->campo6 = $c->campo6 . '%';
            }
        }
        
        return $collection;
    }
    */

    public static function sqlString($search)
    {
        $string = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where('inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_productos.id AS CÓDIGO',
                'inv_productos.descripcion AS DESCRIPCIÓN',
                'inv_productos.unidad_medida1 AS UM-1',
                'inv_grupos.descripcion AS GRUPO_INVENTARIO',
                'inv_productos.precio_compra AS PRECIO_COMPRA',
                'inv_productos.precio_venta AS PRECIO_VENTA',
                'contab_impuestos.tasa_impuesto AS IVA',
                'inv_productos.tipo AS TIPO',
                'inv_productos.estado AS ESTADO'
            )
            ->where("inv_productos.id", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.unidad_medida1", "LIKE", "%$search%")
            ->orWhere("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_compra", "LIKE", "%$search%")
            ->orWhere("inv_productos.precio_venta", "LIKE", "%$search%")
            ->orWhere("contab_impuestos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("inv_productos.tipo", "LIKE", "%$search%")
            ->orWhere("inv_productos.estado", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PRODUCTOS";
    }

    public static function get_datos_basicos( $grupo_inventario_id, $estado, $items_a_mostrar = null )
    {

        $array_wheres = [ 
                            ['inv_productos.core_empresa_id' ,'=', Auth::user()->empresa_id],
                            ['inv_productos.estado' ,'=', $estado ]
                        ];

        if ( $grupo_inventario_id != '')
        {
          $array_wheres = array_merge( $array_wheres, ['inv_productos.inv_grupo_id' => $grupo_inventario_id ] );
        }

        if ( $items_a_mostrar != null && $items_a_mostrar != '')
        {
            if ( $items_a_mostrar == 'sin_codigo_barras' )
            {
                $array_wheres = array_merge( $array_wheres, ['inv_productos.codigo_barras' => '' ] );
            }else{
                $array_wheres = array_merge( $array_wheres, [['inv_productos.codigo_barras', '<>', '' ]] );
            }
        }

        $registros = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                                ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
                                ->where( $array_wheres )
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
                                            'inv_productos.codigo_barras',
                                            'inv_productos.inv_grupo_id')
                                ->orderBy('inv_productos.inv_grupo_id','ASC')
                                ->get();

        foreach ($registros as $item)
        {
            $item->tasa_impuesto = Impuesto::get_tasa( $item->id, 0, 0 );
        }

        return $registros;
    }

    public static function get_grupos_pagina_web()
    {
        $grup = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')->select('inv_grupos.id','inv_grupos.descripcion AS grupo_descripcion','inv_grupos.imagen')->where('inv_productos.mostrar_en_pagina_web',1)->get();
        return $grup->groupBy('grupo_descripcion')->all();
    }


    public static function get_datos_pagina_web( $grupo_inventario_id, $estado, $count = 16, $busqueda=false)
    {
        if ( $grupo_inventario_id == '')
        {
          $grupo_inventario_id = '%'.$grupo_inventario_id.'%';
          $operador1 = 'LIKE';
        }else{
          $operador1 = '=';
        }
        
        if(!$busqueda)
        {
            $busqueda = '';
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
                                'inv_grupos.imagen AS grupo_imagen',
                                'inv_productos.precio_compra',
                                'inv_productos.precio_venta',
                                'contab_impuestos.tasa_impuesto',
                                'inv_productos.tipo',
                                'inv_productos.estado',
                                'inv_productos.imagen',
                                'inv_productos.mostrar_en_pagina_web',
                                'inv_productos.codigo_barras')
                    ->where('inv_productos.mostrar_en_pagina_web',1)
                    ->where('inv_productos.descripcion','LIKE','%'.$busqueda.'%')
                    ->orderBy('grupo_descripcion', 'ASC')
                    ->paginate( $count );
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
