<?php

namespace App\Inventarios;

use App\Compras\Proveedor;
use Illuminate\Database\Eloquent\Model;

/*
        -------------------  OJO ----------------
    CORREGIR PARA LOS CLIENTES NO LOGUEADOS EN LA WEB
    SE COMENTÓ LA LÍNEA DE PEDIR AUTENCIACIÓN
*/

use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;

use App\Contabilidad\Impuesto;
use App\Inventarios\Indumentaria\PrefijoReferencia;
use App\Inventarios\Services\CodigoBarras;
use App\Sistema\Campo;
use App\Sistema\Services\CrudService;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\Services\PricesServices;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class InvProducto extends Model
{
    //protected $table = 'inv_productos'; 

    // tipo = { producto | servicio }
    protected $fillable = ['core_empresa_id','descripcion','tipo','unidad_medida1','unidad_medida2','categoria_id','inv_grupo_id','impuesto_id','precio_compra','precio_venta','estado','referencia','codigo_barras','imagen','mostrar_en_pagina_web','creado_por','modificado_por', 'detalle', 'prefijo_referencia_id'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código',  'Referencia', 'Descripción', 'U.M.', 'Grupo inventario', 'IVA', 'Tipo', 'Mostrar en Página Web', 'Cod. Barras', 'Estado'];

    public $urls_acciones = '{"eliminar":"web_eliminar/id_fila"}';

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

    public function prefijo_referencia()
    {
        return $this->belongsTo(PrefijoReferencia::class, 'prefijo_referencia_id');
    }

    public function item_mandatario()
    {
        $mandatario_id = MandatarioTieneItem::where('item_id', $this->id)
            ->value('mandatario_id');

        return ItemMandatario::find($mandatario_id);
    }

    public function get_url_imagen()
    {
        if ( $this->imagen != '' && $this->imagen != null ) {
            return config('configuracion.url_instancia_cliente')."/storage/app/inventarios/" . $this->imagen;
        }       

        $mandatario_id = MandatarioTieneItem::where('item_id', $this->id)
            ->value('mandatario_id');

        $mandatario = ItemMandatario::find($mandatario_id);

        if ( $mandatario != null)
        {
            if ( $mandatario->imagen != '' && $mandatario->imagen != null)
            {
                return config('configuracion.url_instancia_cliente')."/storage/app/inventarios/" . $mandatario->imagen;
            }
        }

        return '';
    }

    /**
     * 
     */
    public function get_unidad_medida1()
    {
        $campo = Campo::find(79);
        $opciones = json_decode($campo->opciones, true);

        return $opciones[$this->unidad_medida1] ?? $this->unidad_medida1;
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

    public function get_value_to_show( $ocultar_id = false )
    {
        $descripcion_item = $this->descripcion;

        $descripcion_item .= $this->get_color();

        $talla = $this->get_talla();
        
        $referencia = '';
        if( (int)config('inventarios.mostrar_referencia_en_descripcion_items') && $this->referencia != '')
        {
            if($this->referencia != '')
            {
                $referencia = ' - ' . $this->referencia;
            }
        }
        
        $codigo_proveedor = $this->get_codigo_proveedor();

        $prefijo = $this->id . ' ';

        if ( $ocultar_id )
        {
            $prefijo = '';
        }

        if (config('inventarios.codigo_principal_manejo_productos') == 'referencia' && (int)config('inventarios.mostrar_referencia_en_descripcion_items'))
        {
            $prefijo = $this->referencia . ' ';
            $referencia = '';
        }

        $descripcion_item .= $talla . $referencia . $codigo_proveedor . ' (' . $this->get_unidad_medida1() . ')';

        return $prefijo . $descripcion_item;
    }

    public function get_value_to_show_interno( $ocultar_id = false )
    {
        $descripcion_item = $this->descripcion;

        $descripcion_item .= $this->get_color();

        $descripcion_item .= $this->get_tipo_material();

        $talla = $this->get_talla();
        
        $referencia = '';
        if( (int)config('inventarios.mostrar_referencia_en_descripcion_items') && $this->referencia != '')
        {
            if($this->referencia != '')
            {
                $referencia = ' - ' . $this->referencia;
            }
        }
        
        $codigo_proveedor = $this->get_codigo_proveedor();

        $prefijo = $this->id . ' ';

        if ( $ocultar_id )
        {
            $prefijo = '';
        }

        if (config('inventarios.codigo_principal_manejo_productos') == 'referencia' && (int)config('inventarios.mostrar_referencia_en_descripcion_items'))
        {
            $prefijo = $this->referencia . ' ';
            $referencia = '';
        }

        $descripcion_item .= $talla . $referencia . $codigo_proveedor;

        return $prefijo . $descripcion_item;
    }

    /**
     * 
     */
    public function get_talla()
    {        
        $talla = '';
        if( $this->unidad_medida2 != '' )
        {
            $talla = ' - ' . $this->unidad_medida2;
        }

        return $talla;
    }

    /**
     * 
     */
    public function get_color()
    {        
        $color = '';
        if( $this->item_mandatario() != null )
        {
            $color = ' ' . $this->item_mandatario()->paleta_color->descripcion;
        }

        return $color;
    }

    /**
     * 
     */
    public function get_tipo_material()
    {        
        $tipo_material = '';
        if( $this->item_mandatario() != null )
        {
            $tipo_material = ' ' . $this->item_mandatario()->tipo_material->descripcion;
        }

        return $tipo_material;
    }

    /**
     * 
     */
    public function get_codigo_proveedor()
    {
        if ( !Schema::hasTable( 'compras_proveedores' ) )
        {
            return '';
        }

        $codigo_proveedor = '';
        if( (int)config('inventarios.items_mandatarios_por_proveedor') )
        {
            $proveedor = Proveedor::find($this->categoria_id);
            if ( $proveedor != null ) {
                $codigo_proveedor = ' - ' . $proveedor->codigo;
            }            
        }

        return $codigo_proveedor;
    }

    /**
     * 
     */
    public function tasa_impuesto()
    {
        $impuesto = $this->impuesto;
        
        if( is_null( $impuesto ) )
        {
            return 0;
        }

        return $impuesto->tasa_impuesto;
    }

    /**
     * 
     */
    public function get_costo_promedio( $bodega_id = 0 )
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

    /**
     * 
     */
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

    /**
     * 
     */
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
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9, $c->campo10], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        foreach( $nuevaColeccion AS $register_collect )
        {
            $item = InvProducto::find( $register_collect->campo1 );
            $register_collect->campo4 = $item->get_unidad_medida1();
            $register_collect->campo3 = $item->get_value_to_show(true);
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
                'inv_productos.unidad_medida2 AS TALLA',
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
            ->orWhere("inv_productos.unidad_medida2", "LIKE", "%$search%")
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
                                            'inv_productos.unidad_medida2',
                                            'inv_grupos.descripcion AS grupo_descripcion',
                                            'inv_grupos.mostrar_en_pagina_web AS mostrar_grupo_en_pagina_web',
                                            'inv_productos.precio_compra',
                                            'inv_productos.precio_venta',
                                            'inv_productos.tipo',
                                            'inv_productos.impuesto_id',
                                            'inv_productos.categoria_id',
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
            }

            $item->costo_promedio = $costo_prom;
            $item->costo_promedio_mas_iva = $costo_prom * (1 + $tasa_impuesto / 100);
            $item->existencia_actual = $existencia_actual;

            $item->precio_venta = ListaPrecioDetalle::get_precio_producto( config('ventas.lista_precios_id'), date('Y-m-d'), $item->id );

            $item->codigo_proveedor = '';
            if ( (int)$item->categoria_id != 0) {
                $proveedor = Proveedor::find( (int)$item->categoria_id );
                if ($proveedor != null) {
                    $item->codigo_proveedor = $proveedor->codigo;
                }
            }

            $item->unidad_medida1 = $item->get_unidad_medida1();
            
            $item->descripcion .= $item->get_color();
        }

        return $registros;
    }

    public static function get_datos_basicos_ordenados( $grupo_inventario_id, $estado, $items_a_mostrar, $bodega_id, $ordenar_por, $inv_producto_id, $tipo_prenda_id )
    {
        $array_wheres = [ 
                            ['inv_productos.core_empresa_id' ,'=', Auth::user()->empresa_id],
                            ['inv_productos.estado' ,'=', $estado ]
                        ];

        if ( (int)$inv_producto_id != 0 ) {
            $array_wheres = array_merge( $array_wheres, ['inv_productos.id' => (int)$inv_producto_id ] );
        }else{
            
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
        }

        $registros = InvProducto::with('impuesto')->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                                ->where( $array_wheres )
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.descripcion',
                                            'inv_productos.descripcion as label',
                                            'inv_productos.unidad_medida1',
                                            'inv_productos.unidad_medida2',
                                            'inv_grupos.descripcion AS grupo_descripcion',
                                            'inv_productos.precio_compra',
                                            'inv_productos.precio_venta',
                                            'inv_productos.tipo',
                                            'inv_productos.impuesto_id',
                                            'inv_productos.categoria_id',
                                            'inv_productos.estado',
                                            'inv_productos.imagen',
                                            'inv_productos.mostrar_en_pagina_web',
                                            'inv_productos.codigo_barras',
                                            'inv_productos.referencia',
                                            'inv_productos.inv_grupo_id')
                                ->orderBy($ordenar_por,'ASC')
                                ->get();

        foreach ($registros as $key => $item)
        {
            if ( $tipo_prenda_id != 0 )
            {
                $item_mandatario = $item->item_mandatario();
                if ( $item_mandatario != null )
                {
                    if( $item_mandatario->tipo_prenda_id != $tipo_prenda_id )
                    {
                        $registros->forget($key);
                        continue;
                    }
                }
            }
            
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

            $item->unidad_medida1 = $item->get_unidad_medida1();

            $item->descripcion_prenda = $item->descripcion . ' ' . $item->get_color() . ' ' . $item->get_talla();

            if ($item->descripcion_prenda == null) {
                dd($item->descripcion, $item->get_color(), $item->get_talla());
            }
            
            $item->descripcion = $item->get_value_to_show(true);
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
                                'inv_productos.unidad_medida2',
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
                                'inv_productos.unidad_medida2',
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
            
            $item->unidad_medida1 = $item->get_unidad_medida1();
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
            $vec[$opcion->id] = $opcion->get_value_to_show();
        }

        return $vec;
    }

    public static function store_adicional($datos, $registro)
    {
        /*
        */
        if ( $registro->codigo_barras == '' )
        {
            $registro->codigo_barras = (new CodigoBarras($registro->id, 0, 0, 0))->barcode;
            $registro->save();
        }
        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $data = [
                'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                'inv_producto_id' => $registro->id,
                'fecha_activacion' => date('Y-m-d'),
                'precio' => $registro->precio_venta
            ];
            (new PricesServices())->create_item_price( $data );
        }
    }

    public function update_adicional( $datos, $id )
    {
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $datos['fecha_activacion'] = date('Y-m-d');
            $datos['inv_producto_id'] = $id;
            $datos['lista_precios_id'] = (int)config('ventas.lista_precios_id');

            (new PricesServices())->create_or_update_item_price( $datos );
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

    public function validar_eliminacion($id, $eliminar_precios = true )
    {
        if ($eliminar_precios) {
            $reg_precios_actuales = ListaPrecioDetalle::where([
                ['lista_precios_id', '=', (int)config('ventas.lista_precios_id')],
                ['inv_producto_id', '=', $id]
            ])
            ->get();

            foreach ($reg_precios_actuales as $reg_detalle_precio)
            {
                $reg_detalle_precio->delete();
            }
        }

        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"compras_doc_registros",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene documentos de Compras relacionados."
                                },
                            "1":{
                                    "tabla":"compras_movimientos",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene movimientos de Compras relacionados."
                                },
                            "2":{
                                    "tabla":"contab_movimientos",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene movimientos de Contabilidad relacionados."
                                },
                            "3":{
                                    "tabla":"inv_costo_prom_productos",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene Costo Promedio relacionado."
                                },
                            "4":{
                                    "tabla":"inv_doc_registros",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene Registros de Inventarios relacionados."
                                },
                            "5":{
                                    "tabla":"inv_min_stocks",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene Registros de Stock mínimo relacionados."
                                },
                            "6":{
                                    "tabla":"inv_movimientos",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene movimientos de Inventarios relacionados."
                                },
                            "7":{
                                    "tabla":"vtas_doc_registros",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene Registros de Ventas relacionados."
                                },
                            "8":{
                                    "tabla":"vtas_listas_dctos_detalles",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene registros de Descuentos relacionados."
                                },
                            "9":{
                                    "tabla":"inv_items_desarmes_automaticos",
                                    "llave_foranea":"item_producir_id",
                                    "mensaje":"Ítem como Producto terminado en Ensambles automáticos."
                                },
                            "10":{
                                    "tabla":"vtas_movimientos",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene movimientos de Ventas relacionados."
                                },
                            "11":{
                                    "tabla":"vtas_pos_doc_registros",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene registros de Ventas POS relacionados."
                                },
                            "12":{
                                    "tabla":"vtas_pos_movimientos",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene movimientos de Ventas POS relacionados."
                                },
                            "13":{
                                    "tabla":"teso_cartera_estudiantes",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene registros en Cartera de estudiantes relacionados."
                                },
                            "14":{
                                    "tabla":"inv_recetas_cocina",
                                    "llave_foranea":"item_platillo_id",
                                    "mensaje":"Ítem está relacionado como Producto terminado en Ensambles."
                                },
                            "15":{
                                    "tabla":"inv_recetas_cocina",
                                    "llave_foranea":"item_ingrediente_id",
                                    "mensaje":"Ítem está relacionado como Insumo en Ensambles."
                                },
                            "16":{
                                    "tabla":"inv_ficha_producto",
                                    "llave_foranea":"producto_id",
                                    "mensaje":"Ítem tiene registros de Ficha técnica relacionados."
                                },
                            "17":{
                                    "tabla":"inv_items_desarmes_automaticos",
                                    "llave_foranea":"item_consumir_id",
                                    "mensaje":"Ítem como Insumo en Ensambles automáticos."
                                },
                            "18":{
                                    "tabla":"vtas_listas_precios_detalles",
                                    "llave_foranea":"inv_producto_id",
                                    "mensaje":"Ítem tiene registros de Precios relacionados."
                                },
                            "19":{
                                    "tabla":"inv_mandatario_tiene_items",
                                    "llave_foranea":"item_id",
                                    "mensaje":"Ítem tiene ítems mandatarios relacionados."
                                }
                        }';

        return (new CrudService())->validar_eliminacion_un_registro( $id, $tablas_relacionadas);
    }

}
