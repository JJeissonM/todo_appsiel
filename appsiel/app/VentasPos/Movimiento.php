<?php

namespace App\VentasPos;

use App\Core\Empresa;
use App\Inventarios\InvGrupo;
use App\Inventarios\InvProducto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Movimiento extends Model
{
    protected $table = 'vtas_pos_movimientos';
	protected $fillable = ['pdv_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'remision_doc_encabezado_id', 'cliente_id', 'vendedor_id', 'cajero_id', 'zona_id', 'clase_cliente_id', 'equipo_ventas_id', 'forma_pago', 'fecha_vencimiento', 'orden_compras', 'inv_producto_id', 'inv_bodega_id', 'vtas_motivo_id', 'inv_motivo_id', 'precio_unitario', 'cantidad', 'precio_total', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Producto', 'Precio Unit.', 'Cantidad', 'Precio total', 'Estado'];
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
    public function empresa()
    {
        return $this->belongsTo( Empresa::class, 'core_empresa_id' );
    }
    
    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function item()
    {
        return $this->belongsTo(InvProducto::class,'inv_producto_id');
    }

    public function pdv()
    {
        return $this->belongsTo(Pdv::class,'pdv_id');
    }

    public function categoria_item()
    {
        return InvGrupo::where('id',$this->item->grupo_inventario->id)->get()->first();
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    }

    // Nota: el campo item_category_id debe ser agregado al resultado de las consultas
    public function item_category()
    {
        return $this->belongsTo( InvGrupo::class,'item_category_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $collection = Movimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_pos_movimientos.core_tipo_doc_app_id')
        ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_pos_movimientos.inv_producto_id')
        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_movimientos.core_tercero_id')
        ->where('vtas_pos_movimientos.core_empresa_id', Auth::user()->empresa_id)
        ->select(
            DB::raw('DATE_FORMAT(vtas_pos_movimientos.fecha,"%d-%m-%Y") AS campo1'),
            DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_pos_movimientos.consecutivo) AS campo2'),
            'core_terceros.descripcion AS campo3',
            'inv_productos.descripcion AS campo4',
            'vtas_pos_movimientos.precio_unitario AS campo5',
            'vtas_pos_movimientos.cantidad AS campo6',
            'vtas_pos_movimientos.precio_total AS campo7',
            'vtas_pos_movimientos.estado AS campo8',
            'vtas_pos_movimientos.id AS campo9'
        )
            ->where("vtas_pos_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_movimientos.created_at', 'DESC')
            ->get();

        //hacemos el filtro de $search si $search tiene contenido
        $nuevaColeccion = [];
        if (count($collection) > 0)
        {
            if (strlen($search) > 0)
            {
                $nuevaColeccion = $collection->filter(function ($c) use ($search) {
                    if (self::likePhp([$c->campo1, $c->campo2, $c->campo3, $c->campo4, $c->campo5, $c->campo6, $c->campo7, $c->campo8, $c->campo9], $search)) {
                        return $c;
                    }
                });
            } else {
                $nuevaColeccion = $collection;
            }
        }

        foreach( $nuevaColeccion AS $register_collect )
        {
            $movimiento = Movimiento::find( $register_collect->campo9 );

            $item = $movimiento->item;

            $register_collect->campo4 = $item->descripcion . $item->get_color() . $item->get_talla();
            
            $register_collect->campo5 = '$' . number_format( $register_collect->campo5, 0, ',', '.' );

            $register_collect->campo7 = '$' . number_format( $register_collect->campo7, 0, ',', '.' );
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
        $string = Movimiento::select(
            'vtas_pos_movimientos.fecha AS FECHA',
            'vtas_pos_movimientos.core_empresa_id AS DOCUMENTO',
            'vtas_pos_movimientos.cliente_id AS CLIENTE',
            'vtas_pos_movimientos.inv_producto_id AS PRODUCTO',
            'vtas_pos_movimientos.precio_unitario AS PRECIO_UNIT.',
            'vtas_pos_movimientos.cantidad AS CANTIDAD',
            'vtas_pos_movimientos.precio_total AS PRECIO_TOTAL',
            'vtas_pos_movimientos.estado AS ESTADO'
        )
            ->where("vtas_pos_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cliente_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.inv_producto_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS POS";
    }

	public static function opciones_campo_select()
    {
        $opciones = Movimiento::where('vtas_pos_movimientos.estado','Activo')
                    ->select('vtas_pos_movimientos.id','vtas_pos_movimientos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function get_movimiento_ventas( $fecha_desde, $fecha_hasta, $agrupar_por, $estado, $core_tipo_transaccion_id, $pdv_id )
    {
        switch ( $agrupar_por )
        {
            case 'pdv_id':
                $agrupar_por = 'pdv_id';
                break;
            case 'inv_grupo_id':
                $agrupar_por = 'inv_grupo_id';
                break;
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
                $agrupar_por = 'ninguno';
                break;
        }
        
        $array_wheres = [
            ['vtas_pos_movimientos.core_empresa_id','=', Auth::user()->empresa_id]
        ];
        
        $array_wheres = array_merge($array_wheres,[['vtas_pos_movimientos.core_tipo_transaccion_id','<>', 52]]);

        if ($estado!='Todos') {
            $array_wheres = array_merge($array_wheres,[['vtas_pos_movimientos.estado','=', $estado]]);
        }

        if ($core_tipo_transaccion_id != null ) {
            $array_wheres = array_merge($array_wheres,[['vtas_pos_movimientos.core_tipo_transaccion_id','=', $core_tipo_transaccion_id]]);
        }

        if ($pdv_id != null && $pdv_id != 0 ) {
            $array_wheres = array_merge($array_wheres,[['vtas_pos_movimientos.pdv_id','=', $pdv_id]]);
        }

        if(config('inventarios.codigo_principal_manejo_productos') != 'referencia')
        {
            $raw_producto = 'CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto';
        }
        
        if(config('inventarios.codigo_principal_manejo_productos') == 'referencia')
        {
            $raw_producto = 'CONCAT( inv_productos.referencia, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto';
        }

        $movimiento = Movimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_pos_movimientos.inv_producto_id')
                            ->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_movimientos.core_tercero_id')
                            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_pos_movimientos.clase_cliente_id')
                            ->leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'vtas_pos_movimientos.core_tipo_transaccion_id')
                            ->where($array_wheres)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->select(
                                        'vtas_pos_movimientos.inv_producto_id',
                                        DB::raw($raw_producto),
                                        DB::raw('CONCAT( core_terceros.numero_identificacion, " - ", core_terceros.descripcion ) AS cliente'),
                                        'inv_productos.inv_grupo_id',
                                        'vtas_pos_movimientos.core_tipo_transaccion_id',
                                        'vtas_pos_movimientos.core_tipo_doc_app_id',
                                        'vtas_pos_movimientos.consecutivo',
                                        'vtas_pos_movimientos.cliente_id',
                                        'vtas_pos_movimientos.core_tercero_id',
                                        'vtas_clases_clientes.descripcion AS clase_cliente',
                                        'vtas_pos_movimientos.tasa_impuesto AS tasa_impuesto',
                                        'sys_tipos_transacciones.descripcion AS descripcion_tipo_transaccion',
                                        'vtas_pos_movimientos.pdv_id',
                                        'vtas_pos_movimientos.forma_pago',
                                        'vtas_pos_movimientos.vendedor_id',
                                        'vtas_pos_movimientos.cantidad',
                                        'vtas_pos_movimientos.precio_total',
                                        'vtas_pos_movimientos.base_impuesto_total',// AS base_imp_tot
                                        'vtas_pos_movimientos.tasa_descuento',
                                        'vtas_pos_movimientos.valor_total_descuento',
                                        'vtas_pos_movimientos.creado_por')
                            ->get();

        foreach ($movimiento as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );

            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        if ($agrupar_por == 'ninguno') {
            return $movimiento;
        }

        return $movimiento->groupBy( $agrupar_por );
    }

    public static function get_movimiento_ventas_no_anulado( $pdv_id, $fecha_desde, $fecha_hasta )
    {
        $array_wheres = [
            ['vtas_pos_movimientos.pdv_id','=', $pdv_id],
            ['vtas_pos_movimientos.estado','<>', 'Anulado']
        ];
        
        $array_wheres = array_merge($array_wheres,[['vtas_pos_movimientos.core_tipo_transaccion_id','<>', 52]]); // NO las electronicas

        if(config('inventarios.codigo_principal_manejo_productos') != 'referencia')
        {
            $raw_producto = 'CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto';
        }
        
        if(config('inventarios.codigo_principal_manejo_productos') == 'referencia')
        {
            $raw_producto = 'CONCAT( inv_productos.referencia, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto';
        }

        $movimiento = Movimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_pos_movimientos.inv_producto_id')
                            ->leftJoin('inv_grupos', 'inv_grupos.id', '=', 'inv_productos.inv_grupo_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_pos_movimientos.core_tercero_id')
                            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_pos_movimientos.clase_cliente_id')
                            ->leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'vtas_pos_movimientos.core_tipo_transaccion_id')
                            ->where($array_wheres)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->select(
                                        'vtas_pos_movimientos.inv_producto_id',
                                        DB::raw($raw_producto),
                                        DB::raw('CONCAT( core_terceros.numero_identificacion, " - ", core_terceros.descripcion ) AS cliente'),
                                        'inv_productos.inv_grupo_id',
                                        'vtas_pos_movimientos.core_tipo_transaccion_id',
                                        'vtas_pos_movimientos.core_tipo_doc_app_id',
                                        'vtas_pos_movimientos.consecutivo',
                                        'vtas_pos_movimientos.cliente_id',
                                        'vtas_pos_movimientos.core_tercero_id',
                                        'vtas_clases_clientes.descripcion AS clase_cliente',
                                        'vtas_pos_movimientos.tasa_impuesto AS tasa_impuesto',
                                        'sys_tipos_transacciones.descripcion AS descripcion_tipo_transaccion',
                                        'vtas_pos_movimientos.pdv_id',
                                        'vtas_pos_movimientos.forma_pago',
                                        'vtas_pos_movimientos.vendedor_id',
                                        'vtas_pos_movimientos.cantidad',
                                        'vtas_pos_movimientos.precio_total',
                                        'vtas_pos_movimientos.base_impuesto_total',// AS base_imp_tot
                                        'vtas_pos_movimientos.tasa_descuento',
                                        'vtas_pos_movimientos.valor_total_descuento')
                            ->get();


        foreach ($movimiento as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );

            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        return $movimiento;
    }

    public static function get_documentos_ventas_por_transaccion_arr_estados( $fecha_desde, $fecha_hasta, array $arr_tipo_transaccion_id, array $arr_estado )
    {
        return FacturaPos::whereIn('core_tipo_transaccion_id',$arr_tipo_transaccion_id)
                        ->whereIn('estado', $arr_estado)
                        ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                        ->orderBy('fecha')
                        ->get();
    }
}
