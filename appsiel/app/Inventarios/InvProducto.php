<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

/*
        -------------------  OJO ----------------
    CORREGIR PARA LOS CLIENTES NO LOGUEADOS EN LA WEB
    SE COMENTÓ LA LÍNEA DE PEDIR AUTENCIACIÓN
*/

use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;

use App\Contabilidad\Impuesto;
use App\Inventarios\Services\CodigoBarras;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class InvProducto extends Model
{
    //protected $table = 'inv_productos'; 

    // tipo = { producto | servicio }
    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por', 'detalle'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código',  'Referencia', 'Descripción', 'U.M.', 'Grupo inventario', 'IVA', 'Tipo', 'Mostrar en Página Web', 'Cod. Barras', 'Estado'];

    public function grupo_inventario()
    {
        return $this->belongsTo(InvGrupo::class, 'inv_grupo_id');
    }

    public function get_existencia_actual( $bodega_id, $fecha )
    {
        return InvMovimiento::get_existencia_actual( $this->id, $bodega_id, $fecha );
    }

    public function impuesto()
    {
        return $this->belongsTo('App\Contabilidad\Impuesto', 'impuesto_id');
    }

    public function fichas()
    {
        return $this->hasMany(InvFichaProducto::class, 'producto_id', 'id');
    }
    
    public function ingredientes()
    {
        $lista = RecetaCocina::where('item_platillo_id', $this->id )->get();

        $data = [];
        foreach ($lista as $platillo) {
            $data[] = [
                    'id' => $platillo->id,
                    'ingrediente' => $platillo->item_ingrediente,
                    'cantidad_porcion' => $platillo->cantidad_porcion
                ];
        }
        
        return $data;
    }

    public function get_value_to_show()
    {
        $descripcion_item = $this->descripcion . ' (' . $this->unidad_medida1 . ')';

        $talla = '';
        if( (int)config('inventarios.mostrar_talla_en_descripcion_items') && $this->unidad_medida2 != '' )
        {
            $talla = ' - Talla: ' . $this->unidad_medida2;
        }
        
        $referencia = '';
        if( (int)config('inventarios.mostrar_referencia_en_descripcion_items') && $this->referencia != '')
        {
            $referencia = ' - ' . $this->referencia;
        }

        $descripcion_item .= $talla . $referencia;

        $prefijo = $this->id;
        if (config('inventarios.codigo_principal_manejo_productos') == 'referencia') {
            $prefijo = $this->referencia;
        }

        return $prefijo . ' ' . $descripcion_item;
    }

    public function tasa_impuesto()
    {
        $impuesto = $this->impuesto;
        
        if( is_null( $impuesto ) )
        {
            return 0;
        }

        return $impuesto->tasa_impuesto;
    }

    public function get_costo_promedio( $bodega_id )
    {
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 0)
        {
            $bodega_id = 0;
        }

        $costo_prom = InvCostoPromProducto::where([
                                                    ['inv_bodega_id','=',$bodega_id],
                                                    ['inv_producto_id','=', $this->id]
                                                ])
                                        ->value('costo_promedio');

        if ( is_null( $costo_prom ) || $costo_prom <= 0 )
        {
            $costo_prom = $this->precio_compra;
        }

        return $costo_prom;
    }

    // Only Store - Not calculate
    public function set_costo_promedio( $bodega_id, $costo_prom )
    {
        $array_wheres = [ 
            ['inv_producto_id','=', $this->id]
        ];

        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 0)
        {
            $bodega_id = 0;
        }
        
        $array_wheres = array_merge( $array_wheres, ['inv_bodega_id' => $bodega_id] );
        
        $registro_costo_prom = InvCostoPromProducto::where( $array_wheres )
                                        ->get()
                                        ->first();

        $costo_prom = abs( $costo_prom );
        if ( is_null( $registro_costo_prom ) )
        {
            $registro_costo_prom = new InvCostoPromProducto();
            $registro_costo_prom->inv_bodega_id = $bodega_id;
            $registro_costo_prom->inv_producto_id = $this->id;
            $registro_costo_prom->costo_promedio = $costo_prom;
            $registro_costo_prom->save();
        }else{
            $registro_costo_prom->costo_promedio = $costo_prom;
            $registro_costo_prom->save();
        }
        
        $this->precio_compra = $costo_prom;
        $this->save();
    }

    public function get_productos($tipo)
    {
        $opciones = InvProducto::where('estado', 'Activo')
            ->where('tipo', 'LIKE', '%' . $tipo . '%')
            ->get();
        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $referencia = '';
            if($opcion->referencia != '')
            {
                $referencia = ' - ' . $opcion->referencia;
            }

            // Sobreescribir
            if(config('inventarios.codigo_principal_manejo_productos') == 'item_id')
            {
                $referencia = '';
            }

            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion . $referencia;
        }

        return $vec;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection =  InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
            ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
            ->where( 'inv_productos.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_productos.tipo', 'producto')
            ->select(
                'inv_productos.id AS campo1',
                'inv_productos.referencia AS campo2',
                'inv_productos.descripcion AS campo3',
                'inv_productos.unidad_medida1 AS campo4',
                'inv_grupos.descripcion AS campo5',
                'contab_impuestos.tasa_impuesto AS campo6',
                'inv_productos.tipo AS campo7',
                'inv_productos.mostrar_en_pagina_web AS campo8',
                'inv_productos.codigo_barras AS campo9',
                'inv_productos.estado AS campo10',
                'inv_productos.id AS campo11'
            )
            ->orderBy('inv_productos.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        $request = request(); //obtenemos el Request para obtener la url y la query builder

        if (empty($nuevaColeccion)) {
            return $array = new LengthAwarePaginator([], 1, 1, 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);
        }

        //obtenemos el numero de la página actual, por defecto 1
        $page = 1;
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
        }
        $total = count($nuevaColeccion); //Total para contar los registros mostrados
        $starting_point = ($page * $nro_registros) - $nro_registros; // punto de inicio para mostrar registros
        $array = $nuevaColeccion->slice($starting_point, $nro_registros); //indicamos desde donde y cuantos registros mostrar
        $array = new LengthAwarePaginator($array, $total, $nro_registros, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]); //finalmente se pagina y organiza la coleccion a devolver con todos los datos

        return $array;
    }

    /**
     * SQL Like operator in PHP.
     * Returns TRUE if match else FALSE.
     * @param array $valores_campos_seleccionados de campos donde se busca
     * @param string $searchTerm termino de busqueda
     * @return bool
     */
    public static function likePhp($valores_campos_seleccionados, $searchTerm)
    {
        $encontrado = false;
        $searchTerm = str_slug($searchTerm); // Para eliminar acentos
        foreach ($valores_campos_seleccionados as $valor_campo) {
            $str = str_slug($valor_campo);
            $pos = strpos($str, $searchTerm);
            if ($pos !== false) {
                $encontrado = true;
            }
        }
        return $encontrado;
    }

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
                'inv_productos.codigo_barras AS CODIGO_BARRAS',
                'inv_productos.referencia AS REFERENCIA',
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
            ->orWhere("inv_productos.codigo_barras", "LIKE", "%$search%")
            ->orWhere("inv_productos.referencia", "LIKE", "%$search%")
            ->orderBy('inv_productos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PRODUCTOS";
    }

    public static function get_datos_basicos( $grupo_inventario_id, $estado, $items_a_mostrar = null, $bodega_id = null )
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

        $registros = InvProducto::with('impuesto')->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                                ->where( $array_wheres )
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.descripcion',
                                            'inv_productos.unidad_medida1',
                                            'inv_grupos.descripcion AS grupo_descripcion',
                                            'inv_productos.precio_compra',
                                            'inv_productos.precio_venta',
                                            'inv_productos.tipo',
                                            'inv_productos.impuesto_id',
                                            'inv_productos.estado',
                                            'inv_productos.imagen',
                                            'inv_productos.mostrar_en_pagina_web',
                                            'inv_productos.codigo_barras',
                                            'inv_productos.referencia',
                                            'inv_productos.inv_grupo_id')
                                ->orderBy('inv_grupos.descripcion','ASC')
                                ->get();

        foreach ($registros as $item)
        {
            $tasa_impuesto = 0;
            if ($item->impuesto != null) {
                $tasa_impuesto = $item->impuesto->get_tasa2($item->id, 0, 0);
            }
            $item->tasa_impuesto = $tasa_impuesto;

            $costo_prom = $item->precio_compra;
            $existencia_actual = 0;
            if ( $bodega_id != null )
            {
                $costo_prom = $item->get_costo_promedio( $bodega_id );
                //$existencia_actual = $item->get_existencia_actual( $bodega_id, date('Y-m-d') );
            }

            $item->costo_promedio = $costo_prom;
            $item->costo_promedio_mas_iva = $costo_prom * (1 + $tasa_impuesto / 100);
            $item->existencia_actual = $existencia_actual;

            $item->precio_venta = ListaPrecioDetalle::get_precio_producto( config('ventas.lista_precios_id'), date('Y-m-d'), $item->id );
        }

        return $registros;
    }

    public static function get_datos_basicos_ordenados( $grupo_inventario_id, $estado, $items_a_mostrar, $bodega_id, $ordenar_por )
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

        $registros = InvProducto::with('impuesto')->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                                ->where( $array_wheres )
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.descripcion',
                                            'inv_productos.unidad_medida1',
                                            'inv_grupos.descripcion AS grupo_descripcion',
                                            'inv_productos.precio_compra',
                                            'inv_productos.precio_venta',
                                            'inv_productos.tipo',
                                            'inv_productos.impuesto_id',
                                            'inv_productos.estado',
                                            'inv_productos.imagen',
                                            'inv_productos.mostrar_en_pagina_web',
                                            'inv_productos.codigo_barras',
                                            'inv_productos.referencia',
                                            'inv_productos.inv_grupo_id')
                                ->orderBy($ordenar_por,'ASC')
                                ->get();

        foreach ($registros as $item)
        {
            $tasa_impuesto = 0;
            if ($item->impuesto != null) {
                $tasa_impuesto = $item->impuesto->get_tasa2($item->id, 0, 0);
            }
            $item->tasa_impuesto = $tasa_impuesto;

            $costo_prom = $item->precio_compra;
            $existencia_actual = 0;
            if ( $bodega_id != null )
            {
                $costo_prom = $item->get_costo_promedio( $bodega_id );
                //$existencia_actual = $item->get_existencia_actual( $bodega_id, date('Y-m-d') );
            }

            $item->costo_promedio = $costo_prom;
            $item->existencia_actual = $existencia_actual;

            $item->precio_venta = ListaPrecioDetalle::get_precio_producto( config('ventas.lista_precios_id'), date('Y-m-d'), $item->id );
        }

        return $registros;
    }

    public static function get_grupos_pagina_web()
    {
        $grup = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')->select('inv_grupos.id','inv_grupos.descripcion AS grupo_descripcion','inv_grupos.imagen','inv_productos.estado')->where('inv_productos.mostrar_en_pagina_web',1)->get();
        return $grup->groupBy('grupo_descripcion')->all();
    }


    public static function get_producto_pagina_web($id)
    {
        $producto = InvProducto::leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                    ->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
                    ->where('inv_productos.id',$id)
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
                                'inv_productos.referencia',
                                'inv_productos.mostrar_en_pagina_web',
                                'inv_productos.codigo_barras')
                    ->orderBy('grupo_descripcion', 'ASC')
                    ->get()
                    ->first();

        
            $producto->precio_venta = ListaPrecioDetalle::get_precio_producto( config('pagina_web.lista_precios_id'), date('Y-m-d'), $id );

            $producto->descuento = ListaDctoDetalle::get_descuento_producto( config('pagina_web.lista_descuentos_id'), date('Y-m-d'), $id );

            $producto->valor_descuento = $producto->precio_venta * ( $producto->descuento / 100);

        return $producto;
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
                                'inv_productos.referencia',
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

            $item->valor_descuento = $item->precio_venta * ( $item->descuento / 100);
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
            $referencia = '';
            if($opcion->referencia != '')
            {
                $referencia = ' - ' . $opcion->referencia;
            }
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion . $referencia;
        }

        return $vec;
    }    

    public static function store_adicional($datos, $registro)
    {
        if ( $registro->codigo_barras == '' )
        {
            $registro->codigo_barras = (new CodigoBarras($registro->id, 0, 0, 0))->barcode;
            $registro->save();
        }
        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            ListaPrecioDetalle::create([
                'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                'inv_producto_id' => $registro->id,
                'fecha_activacion' => date('Y-m-d'),
                'precio' => $registro->precio_venta
            ]);
        }
    }

    public function update_adicional( $datos, $id )
    {
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $nuevo_precio_venta = 0;
            if (isset($datos['precio_venta'])) {
                $nuevo_precio_venta = $datos['precio_venta'];
            }

            $reg_precio_actual = ListaPrecioDetalle::where([
                ['lista_precios_id', '=', (int)config('ventas.lista_precios_id')],
                ['inv_producto_id', '=', $id]
            ])
            ->get()
            ->last();

            if ($reg_precio_actual == null) {
                ListaPrecioDetalle::create([
                    'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                    'inv_producto_id' => $id,
                    'fecha_activacion' => date('Y-m-d'),
                    'precio' => $nuevo_precio_venta
                ]);
            }else{
                if ($nuevo_precio_venta != $reg_precio_actual->precio) {
                    $reg_precio_actual->precio = $nuevo_precio_venta;
                    $reg_precio_actual->save();
                }
            }
        }
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

    public function get_precio_venta()
    {
        $detalle_lista_precios = ListaPrecioDetalle::where([
            ['lista_precios_id', '=', (int)config('ventas.lista_precios_id')],
            ['inv_producto_id','=',$this->id]
        ])->orderBy('fecha_activacion')->get()
        ->last();

        if ($detalle_lista_precios == null) {
            return $this->precio_venta;
        }

        return $detalle_lista_precios->precio;
    }

}
