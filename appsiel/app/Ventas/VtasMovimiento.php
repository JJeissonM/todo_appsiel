<?php

namespace App\Ventas;

use App\Inventarios\InvBodega;
use App\Inventarios\InvMotivo;
use Illuminate\Database\Eloquent\Model;

use App\Inventarios\InvProducto;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VtasMovimiento extends Model
{
    // base_impuesto: es unitario y siempre positivo
    // valor_impuesto: es unitario
    // precio_total: tiene signo dependiendo de la operacion (ventas +, notas crédito -)
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'vtas_motivo_id', 'fecha', 'core_tercero_id', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'cliente_id', 'vendedor_id', 'zona_id', 'clase_cliente_id', 'equipo_ventas_id', 'forma_pago', 'fecha_vencimiento', 'orden_compras', 'inv_motivo_id', 'inv_bodega_id', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'codigo_referencia_tercero', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'detalle'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Producto', 'Cliente', 'Vend.', 'Precio unit.', 'Cantidad', 'Precio total', 'IVA', 'Base IVA Total'];

    public $vistas = '{"index":"layouts.index3"}';

    public function producto()
    {
        return $this->belongsTo(InvProducto::class,'inv_producto_id');
    }

    public function cliente()
    {
        return $this->belongsTo( Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    public function bodega()
    {
        return $this->belongsTo(InvBodega::class, 'inv_bodega_id');
    }

    public function motivo()
    {
        return $this->belongsTo(InvMotivo::class, 'vtas_motivo_id');
    }

    public function tercero()
    {
        return $this->belongsTo( 'App\Core\Tercero', 'core_tercero_id');
    }
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }
    
    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    }
    
    public function encabezado_documento()
    {
        return VtasDocEncabezado::where([
                                    ['core_tipo_transaccion_id','=',$this->core_tipo_transaccion_id],
                                    ['core_tipo_doc_app_id','=',$this->core_tipo_doc_app_id],
                                    ['consecutivo','=',$this->consecutivo]
                                ])
                                ->get()
                                ->first();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS campo2';

        $registros = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->leftJoin('vtas_vendedores', 'vtas_vendedores.id', '=', 'vtas_movimientos.vendedor_id')
            ->leftJoin('core_terceros as vendedores', 'vendedores.id', '=', 'vtas_vendedores.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_movimientos.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw($select_raw),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'vendedores.descripcion AS campo5',
                'vtas_movimientos.precio_unitario AS campo6',
                'vtas_movimientos.cantidad AS campo7',
                'vtas_movimientos.precio_total AS campo8',
                'vtas_movimientos.tasa_impuesto AS campo9',
                'vtas_movimientos.base_impuesto_total AS campo10',
                'vtas_movimientos.id AS campo11'
            )
            ->where("vtas_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.base_impuesto_total", "LIKE", "%$search%")
            ->orderBy('vtas_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS DOCUMENTO';

        $string = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_movimientos.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw($select_raw),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS PRODUCTO'),
                'core_terceros.descripcion AS CLIENTE',
                'vtas_movimientos.precio_unitario AS PRECIO_UNIT',
                'vtas_movimientos.cantidad AS CANTIDAD',
                'vtas_movimientos.precio_total AS PRECIO_TOTAL',
                'vtas_movimientos.tasa_impuesto AS IVA',
                'vtas_movimientos.base_impuesto_total AS BASE_IVA_TOTAL'
            )
            ->orWhere("vtas_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.base_impuesto_total", "LIKE", "%$search%")
            ->orderBy('vtas_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS DE VENTA";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS campo2';

        $collection = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->leftJoin('vtas_vendedores', 'vtas_vendedores.id', '=', 'vtas_movimientos.vendedor_id')
            ->leftJoin('core_terceros as vendedores', 'vendedores.id', '=', 'vtas_vendedores.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_movimientos.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw($select_raw),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'vendedores.descripcion AS campo5',
                'vtas_movimientos.precio_unitario AS campo6',
                'vtas_movimientos.cantidad AS campo7',
                'vtas_movimientos.precio_total AS campo8',
                'vtas_movimientos.tasa_impuesto AS campo9',
                'vtas_movimientos.base_impuesto_total AS campo10',
                'vtas_movimientos.id AS campo11'
            )
            ->orderBy('vtas_movimientos.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0) {
            if (strlen($search) > 0) {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9, $c->campo10, $c->campo11], $search)) {
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

    public static function get_precios_ventas($fecha_desde, $fecha_hasta, $producto_id, $operador1, $cliente_id, $operador2)
    {
        return VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->where('vtas_movimientos.inv_producto_id', $operador1, $producto_id)
            ->where('vtas_movimientos.cliente_id', $operador2, $cliente_id)
            ->select(
                'vtas_movimientos.inv_producto_id', 
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS producto'), 
                DB::raw('CONCAT( core_terceros.numero_identificacion, " - ", core_terceros.descripcion ) AS cliente'), 
                DB::raw('SUM(vtas_movimientos.cantidad) AS cantidad'), 
                DB::raw('SUM(vtas_movimientos.base_impuesto_total) AS base_impuesto_total'))
            ->groupBy('vtas_movimientos.inv_producto_id')
            ->groupBy('vtas_movimientos.cliente_id')
            ->get();
    }

    public static function get_movimiento_ventas( $fecha_desde, $fecha_hasta, $agrupar_por, $core_tipo_transaccion_id = null )
    {
        switch ( $agrupar_por )
        {
            case 'cliente_id':
                $agrupar_por = 'cliente';
                break;
            case 'core_tercero_id':
                $agrupar_por = 'core_tercero_id';
                break;
            case 'inv_producto_id':
                $agrupar_por = 'producto';
                break;
            case 'tasa_impuesto':
                $agrupar_por = 'tasa_impuesto';
                break;
            case 'vendedor_id':
                $agrupar_por = 'vendedor_id';
                break;
            case 'clase_cliente_id':
                $agrupar_por = 'clase_cliente';
                break;
            case 'core_tipo_transaccion_id':
                $agrupar_por = 'descripcion_tipo_transaccion';
                break;
            case 'forma_pago':
                $agrupar_por = 'forma_pago';
                break;
            
            default:
                break;
        }
        
        $array_wheres = [
            ['vtas_movimientos.core_empresa_id','=', Auth::user()->empresa_id]
        ];

        if ($core_tipo_transaccion_id != null ) {
            $array_wheres = array_merge($array_wheres,[['vtas_movimientos.core_tipo_transaccion_id','=', $core_tipo_transaccion_id]]);
        }

        $movimiento = VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
                            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_movimientos.clase_cliente_id')
                            ->leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'vtas_movimientos.core_tipo_transaccion_id')
                            ->where($array_wheres)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->select(
                                        'vtas_movimientos.inv_producto_id',
                                        DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto'),
                                        DB::raw('CONCAT( core_terceros.numero_identificacion, " - ", core_terceros.descripcion ) AS cliente'),
                                        'vtas_movimientos.cliente_id',
                                        'vtas_movimientos.core_tercero_id',
                                        'vtas_clases_clientes.descripcion AS clase_cliente',
                                        'vtas_movimientos.tasa_impuesto AS tasa_impuesto',
                                        'sys_tipos_transacciones.descripcion AS descripcion_tipo_transaccion',
                                        'vtas_movimientos.forma_pago',
                                        'vtas_movimientos.vendedor_id',
                                        'vtas_movimientos.cantidad',
                                        'vtas_movimientos.precio_total',
                                        'vtas_movimientos.base_impuesto_total',// AS base_imp_tot
                                        'vtas_movimientos.tasa_descuento',
                                        'vtas_movimientos.valor_total_descuento')
                            ->get();

        foreach ($movimiento as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );


            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        return $movimiento->groupBy( $agrupar_por );
    }

    public static function get_documentos_ventas_por_transaccion( $fecha_desde, $fecha_hasta, array $arr_tipo_transaccion_id, $estado )
    {        
        $array_wheres = [
            ['vtas_doc_encabezados.core_empresa_id','=', Auth::user()->empresa_id]
        ];

        if ($estado!='Todos') {
            $array_wheres = array_merge($array_wheres,[['vtas_doc_encabezados.estado','=', $estado]]);
        }

        return VtasDocEncabezado::where($array_wheres)
                            ->whereIn('vtas_doc_encabezados.core_tipo_transaccion_id',$arr_tipo_transaccion_id)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->get();
    }

    public static function get_ultimo_precio_producto($cliente_id, $producto_id)
    {
        $registro = VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_movimientos.inv_producto_id', '=', $producto_id)
            ->where('vtas_movimientos.cliente_id', '=', $cliente_id)
            ->select('vtas_movimientos.precio_unitario')
            ->get()
            ->last();

        if (is_null($registro)) {
            return InvProducto::find($producto_id)->precio_venta;
        } else {
            return $registro->precio_unitario;
        }
    }

    public static function mov_ventas_totales_por_fecha( $fecha_inicial, $fecha_final )
    {
        return VtasMovimiento::whereBetween('fecha',[$fecha_inicial, $fecha_final])
                                ->select(
                                            DB::raw('SUM(base_impuesto) as total_ventas'),
                                            DB::raw('SUM(precio_total) as total_ventas_netas'),
                                            'fecha')
                                ->groupBy('fecha')
                                ->orderBy('fecha')
                                ->get();

    }
    
    public static function get_movimiento_entre_fechas( $fecha_desde, $fecha_hasta )
    {
        $movimiento = VtasMovimiento::whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->get();

        foreach ($movimiento as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );


            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        return $movimiento;
    }
}
