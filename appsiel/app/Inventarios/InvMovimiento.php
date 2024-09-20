<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use App\Inventarios\InvMotivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvMovimiento extends Model
{
    protected $fillable = [ 'core_empresa_id', 'inv_doc_encabezado_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'inv_motivo_id', 'inv_bodega_id', 'inv_producto_id', 'costo_unitario', 'cantidad', 'costo_total', 'creado_por', 'modificado_por', 'codigo_referencia_tercero', 'core_tercero_id'];

    public $encabezado_tabla = [ '<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Tercero', 'Producto', 'Bodega', 'Motivo', 'Movimiento', 'Costo unit.', 'Cantidad', 'Costo total', '&nbsp;'];

    public $vistas = '{"index":"layouts.index3"}';
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }
    
    public function producto()
    {
        return $this->belongsTo(InvProducto::class,'inv_producto_id');
    }
    
    public function motivo()
    {
        return $this->belongsTo(InvMotivo::class,'inv_motivo_id');
    }
    
    public function bodega()
    {
        return $this->belongsTo(InvBodega::class,'inv_bodega_id');
    }
    
    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    }

    public function enlace_show_documento()
    {
        $app_id = 8;
        
        $document_header_id = InvDocEncabezado::where([
            [ 'core_tipo_transaccion_id','=',$this->core_tipo_transaccion_id],
            [ 'core_tipo_doc_app_id','=',$this->core_tipo_doc_app_id],
            [ 'consecutivo','=',$this->consecutivo]
        ])->get()->first()->id;

        $enlace = '<a href="' . url( 'inventarios/' . $document_header_id . '?id=' . $app_id . '&id_modelo=' . $this->tipo_transaccion->core_modelo_id . '&id_transaccion=' . $this->core_tipo_transaccion_id ) . '" target="_blank">' . $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo . '</a>';

        return $enlace;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo) AS campo2';

        $registros = InvMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_movimientos.core_tercero_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_movimientos.inv_producto_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_movimientos.inv_bodega_id')
            ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'inv_movimientos.inv_motivo_id')
            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_movimientos.fecha AS campo1',
                DB::raw($select_raw),
                DB::raw('CONCAT(core_terceros.numero_identificacion," - ",core_terceros.descripcion) AS campo3'),
                DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS campo4'),
                'inv_bodegas.descripcion AS campo5',
                'inv_motivos.descripcion AS campo6',
                'inv_motivos.movimiento AS campo7',
                'inv_movimientos.costo_unitario AS campo8',
                'inv_movimientos.cantidad AS campo9',
                'inv_movimientos.costo_total AS campo10',
                'inv_movimientos.id AS campo11',
                'inv_movimientos.id AS campo12'
            )

            ->where("inv_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.numero_identificacion," - ",core_terceros.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.costo_unitario", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.costo_total", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.id", "LIKE", "%$search%")
            ->orderBy('inv_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo) AS DOCUMENTO';

        $string = InvMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_movimientos.core_tercero_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_movimientos.inv_producto_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_movimientos.inv_bodega_id')
            ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'inv_movimientos.inv_motivo_id')
            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_movimientos.fecha AS FECHA',
                DB::raw($select_raw),
                DB::raw('CONCAT(core_terceros.numero_identificacion," - ",core_terceros.descripcion) AS TERCERO'),
                DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS PRODUCTO'),
                'inv_bodegas.descripcion AS BODEGA',
                'inv_motivos.descripcion AS MOTIVO',
                'inv_motivos.movimiento AS MOVIMIENTO',
                'inv_movimientos.costo_unitario AS COSTO_UNIT.',
                'inv_movimientos.cantidad AS CANTIDAD',
                'inv_movimientos.costo_total AS COSTO_TOTAL',
                'inv_movimientos.id AS &nbsp;'
            )

            ->where("inv_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.numero_identificacion," - ",core_terceros.descripcion)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.costo_unitario", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.costo_total", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.id", "LIKE", "%$search%")
            ->orderBy('inv_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS DE INVENTARIO";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo) AS campo2';

        $registros = InvMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_movimientos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_movimientos.core_tercero_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_movimientos.inv_producto_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_movimientos.inv_bodega_id')
            ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'inv_movimientos.inv_motivo_id')
            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_movimientos.fecha AS campo1',
                DB::raw($select_raw),
                DB::raw('CONCAT(core_terceros.numero_identificacion," - ",core_terceros.descripcion) AS campo3'),
                DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS campo4'),
                'inv_bodegas.descripcion AS campo5',
                'inv_motivos.descripcion AS campo6',
                'inv_motivos.movimiento AS campo7',
                'inv_movimientos.costo_unitario AS campo8',
                'inv_movimientos.cantidad AS campo9',
                'inv_movimientos.costo_total AS campo10',
                'inv_movimientos.id AS campo11',
                'inv_movimientos.id AS campo12'
            )
            ->where("inv_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.numero_identificacion," - ",core_terceros.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_motivos.movimiento", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.costo_unitario", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.costo_total", "LIKE", "%$search%")
            ->orWhere("inv_movimientos.id", "LIKE", "%$search%")
            ->orderBy('inv_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function get_movimiento_corte( $fecha_corte, $operador1, $mov_bodega_id, $operador2, $grupo_inventario_id)
    {
        
        if ( $mov_bodega_id != '' ) {
            $orden = 'inv_movimientos.inv_producto_id';
        }else{
            $orden = 'inv_bodegas.descripcion';
        }

        $productos = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                                ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                                ->leftJoin('inv_grupos','inv_grupos.id','=','inv_productos.inv_grupo_id')
                                ->leftJoin('inv_bodegas','inv_bodegas.id','=','inv_doc_encabezados.inv_bodega_id')
                                ->where('inv_doc_encabezados.fecha','<=',$fecha_corte)
                                ->where('inv_movimientos.inv_bodega_id',$operador1,$mov_bodega_id)
                                ->where('inv_grupos.id',$operador2,$grupo_inventario_id)
                                ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->where('inv_productos.estado', 'Activo')
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.descripcion',
                                            'inv_productos.unidad_medida1',
                                            'inv_productos.unidad_medida2',
                                            'inv_productos.estado',
                                            'inv_productos.referencia',
                                            'inv_bodegas.descripcion AS bodega',
                                            DB::raw('sum(inv_movimientos.cantidad) as Cantidad'),
                                            DB::raw('sum(inv_movimientos.costo_total) as Costo') )
                                ->groupBy($orden)
                                ->get()
                                ->toArray();//,'inv_costo_prom_productos.costo_promedio as costo_promedio_ponderado'
        return $productos;
    }

    public static function get_existencia_corte( $array_wheres )
    {
        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                                ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                                ->leftJoin('inv_grupos','inv_grupos.id','=','inv_productos.inv_grupo_id')
                                ->leftJoin('inv_bodegas','inv_bodegas.id','=','inv_doc_encabezados.inv_bodega_id')
                                ->where( $array_wheres )
                                ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->select(
                                            'inv_productos.estado',
                                            'inv_movimientos.*',
                                            DB::raw('sum(inv_movimientos.cantidad) as suma_cantidad'),
                                            DB::raw('sum(inv_movimientos.costo_total) as suma_costo') )
                                ->groupBy('inv_productos.descripcion')
                                ->groupBy('inv_productos.unidad_medida2')
                                ->get();
    }

    public static function get_saldo_inicial($id_producto, $id_bodega, $fecha_inicial, $tercero_id )
    {
        $array_wheres = [
            [ 'inv_movimientos.core_empresa_id', '=', Auth::user()->empresa_id ],
            [ 'inv_movimientos.inv_bodega_id', '=', $id_bodega ],
            [ 'inv_movimientos.inv_producto_id', '=', $id_producto ],
            [ 'inv_doc_encabezados.fecha','<',$fecha_inicial ]
        ];

        if ($tercero_id != 0) {
            $array_wheres = array_merge($array_wheres, [ 'inv_movimientos.core_tercero_id' => $tercero_id ]);
        }

        $sql_saldo_inicial = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                    ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                    ->where( $array_wheres )
                    ->select(DB::raw('sum(inv_movimientos.cantidad) as mCantidad'),DB::raw('sum(inv_movimientos.costo_total) as mCosto'))
                    ->get()
                    ->toArray();
        return $sql_saldo_inicial[0];
    }

    public static function get_saldos_iniciales_items( $grupo_inventario_id, $inv_bodega_id, $fecha_inicial )
    {
        $array_wheres = [ 
                            [ 'inv_doc_encabezados.fecha' ,'<', $fecha_inicial]
                        ];

        if ( $grupo_inventario_id != '')
        {
          $array_wheres = array_merge( $array_wheres, [ 'inv_productos.inv_grupo_id' => $grupo_inventario_id ] );
        }

        if ( $inv_bodega_id != '')
        {
          $array_wheres = array_merge( $array_wheres, [ 'inv_movimientos.inv_bodega_id' => $inv_bodega_id ] );
        }
        
        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                            ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                            ->where( $array_wheres )
                            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->select(
                                        'inv_productos.id AS item_id',
                                        DB::raw('sum(inv_movimientos.cantidad) as cantidad_total_movimiento'),
                                        DB::raw('sum(inv_movimientos.costo_total) as costo_total_movimiento') 
                                    )
                            ->groupBy( 'inv_movimientos.inv_producto_id' )
                            ->get();
    }

    public static function get_suma_movimientos( $grupo_inventario_id, $inv_bodega_id, $fecha_inicial, $fecha_final, $tipo_movimiento )
    {
        $array_wheres = [ 
                            [ 'inv_doc_encabezados.fecha' ,'>=', $fecha_inicial ],
                            [ 'inv_doc_encabezados.fecha' ,'<=', $fecha_final ]
                        ];

        if ( $grupo_inventario_id != '')
        {
          $array_wheres = array_merge( $array_wheres, [ 'inv_productos.inv_grupo_id' => $grupo_inventario_id ] );
        }

        if ( $inv_bodega_id != '')
        {
          $array_wheres = array_merge( $array_wheres, [ 'inv_movimientos.inv_bodega_id' => $inv_bodega_id ] );
        }

        if ( $tipo_movimiento != '')
        {
          $array_wheres = array_merge( $array_wheres, [ 'inv_motivos.movimiento' => $tipo_movimiento ] );
        }

        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                            ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                            ->leftJoin('inv_motivos','inv_motivos.id','=','inv_movimientos.inv_motivo_id')
                            ->where( $array_wheres )
                            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->select(
                                        'inv_productos.id AS item_id',
                                        DB::raw('sum(inv_movimientos.cantidad) as cantidad_total_movimiento'),
                                        DB::raw('sum(inv_movimientos.costo_total) as costo_total_movimiento') 
                                    )
                            ->groupBy( 'inv_movimientos.inv_producto_id' )
                            ->get();
    }

    public static function get_movimiento($id_producto, $id_bodega, $fecha_inicial, $fecha_final )
    {
        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                                ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                                ->leftJoin('inv_motivos','inv_motivos.id','=','inv_movimientos.inv_motivo_id')
                                ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->where('inv_movimientos.inv_bodega_id','=',$id_bodega)
                                ->where('inv_movimientos.inv_producto_id','=',$id_producto)
                                ->whereBetween('inv_doc_encabezados.fecha', [$fecha_inicial, $fecha_final])
                                ->select('inv_doc_encabezados.id',
                                        'inv_doc_encabezados.fecha',
                                        'inv_motivos.movimiento',
                                        'inv_movimientos.inv_doc_encabezado_id',
                                        'inv_movimientos.core_tipo_doc_app_id',
                                        'inv_movimientos.consecutivo',
                                        'inv_movimientos.cantidad',
                                        'inv_movimientos.costo_unitario',
                                        'inv_movimientos.costo_total',
                                        'inv_movimientos.core_tipo_transaccion_id')
                                ->orderBy('inv_movimientos.fecha')
                                ->orderBy('inv_movimientos.created_at')
                                ->get();
    }

    public static function get_movimiento2($id_producto, $id_bodega, $fecha_inicial, $fecha_final, $tercero_id )
    {
        $array_wheres = [
            [ 'inv_movimientos.core_empresa_id', '=', Auth::user()->empresa_id],
            [ 'inv_movimientos.inv_bodega_id', '=', $id_bodega],
            [ 'inv_movimientos.inv_producto_id', '=', $id_producto]
        ];

        if ($tercero_id != 0) {
            $array_wheres = array_merge($array_wheres, [ 'inv_movimientos.core_tercero_id' => $tercero_id]);
        }

        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                                ->leftJoin('inv_motivos','inv_motivos.id','=','inv_movimientos.inv_motivo_id')
                                ->where( $array_wheres )
                                ->whereBetween('inv_movimientos.fecha', [$fecha_inicial, $fecha_final])
                                ->select('inv_movimientos.core_tipo_doc_app_id',
                                        'inv_movimientos.fecha',
                                        'inv_motivos.movimiento',
                                        'inv_movimientos.inv_doc_encabezado_id',
                                        'inv_movimientos.inv_producto_id',
                                        'inv_movimientos.cantidad',
                                        'inv_movimientos.costo_unitario',
                                        'inv_movimientos.costo_total',
                                        'inv_movimientos.core_tipo_transaccion_id',
                                        'inv_movimientos.consecutivo')
                                ->orderBy('inv_movimientos.fecha')
                                ->orderBy('inv_movimientos.created_at')
                                ->get();
    }

    public static function get_movimiento_transacciones_ventas( $fecha_inicial, $fecha_final )
    {
        /*              MOTIVOS DE INVENTARIOS
            ID  Desccrpcion                         Transacción asociada        Movimiento
            17  Remisión de ventas                  Remisión de ventas          salida
            15  Devolución en ventas                Devolución en ventas        entrada
            10  Ventas Estándar                     Venta                       salida
        */

        return InvMovimiento::leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                            ->whereIn( 'inv_movimientos.inv_motivo_id', [ 10, 15, 17] )
                            ->whereBetween('inv_doc_encabezados.fecha', [ $fecha_inicial, $fecha_final ] )
                            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->get();
    }

    public static function get_existencia_producto( $producto_id, $bodega_id, $fecha_corte )
    {
        $fecha_corte = \Carbon\Carbon::parse( $fecha_corte )->format('Y-m-d');

        return InvMovimiento::where('inv_movimientos.inv_bodega_id',$bodega_id)
                    ->where('inv_movimientos.inv_producto_id',$producto_id)
                    ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->where('inv_movimientos.fecha','<=',$fecha_corte)
                    ->select( DB::raw('sum(inv_movimientos.cantidad) as Cantidad'),DB::raw('sum(inv_movimientos.costo_total) as Costo'))
                    ->get()[0];
    }

    public static function get_existencia_actual( $producto_id, $bodega_id, $fecha_corte )
    {
        $fecha_corte = \Carbon\Carbon::parse( $fecha_corte )->format('Y-m-d');
        
        $existencia_actual = InvMovimiento::where('inv_bodega_id','=',$bodega_id)
                                ->where('inv_producto_id','=',$producto_id)
                                ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->where('fecha','<=',$fecha_corte)
                                ->sum('cantidad');

        if ( is_null($existencia_actual) )
        {
            $existencia_actual = 0;
        }

        return $existencia_actual;
    }

    // Retorna un objeto o un valor null
    public static function validar_saldo_movimientos_posteriores( $bodega_id, $producto_id, $fecha, $cantidad_nueva, $saldo_a_la_fecha, $tipo_movimiento, $cantidad_anterior = null)
    {
        // El tipo_movimiento debe indicar el movimiento de la transacción que se está haciendo

        if ( $tipo_movimiento == 'entrada' )
        {
            return [null, null];
        }

        if ( $saldo_a_la_fecha == 'no')
        {
            // La cantidad anterior se debe tener en cuenta para el cálculo del saldo a la fecha
            if ( is_null($cantidad_anterior) )
            {
                $cantidad_anterior = 0;
            }

            $saldo_a_la_fecha = InvMovimiento::get_existencia_actual( $producto_id, $bodega_id, $fecha );
            if ($tipo_movimiento == 'entrada')
            {
                $saldo_a_la_fecha = $saldo_a_la_fecha - $cantidad_anterior + $cantidad_nueva;
            }else{
                // Si es 'salida'
                $saldo_a_la_fecha = $saldo_a_la_fecha + $cantidad_anterior - $cantidad_nueva;
            }

            if ( $saldo_a_la_fecha < 0 )
            {
                return [ (object)[ 'id' => 0], [$saldo_a_la_fecha] ];
            }
        }

        // Cuando se envía un saldo_a_la_fecha, ya viene sumada o restada la nueva_cantidad
        $saldo_anterior =  (float)$saldo_a_la_fecha;
        $vec_saldo[0] = (float)$saldo_a_la_fecha;


        // Se empieza a validar desde la FECHA SIGUIENTE a la fecha de la línea que se está modificando
        $movimiento_posterior = InvMovimiento::get_movimiento($producto_id, $bodega_id, date( "Y-m-d", strtotime($fecha."+ 1 days") ), '2050-12-31' );

        
        $saldo_linea = 0; // validador

        $k = 1;
        
        foreach ($movimiento_posterior as $linea_movimiento)
        {/**/
            $saldo_linea = $saldo_anterior + $linea_movimiento->cantidad; // $linea_movimiento->cantidad puede ser + ó -

            $vec_saldo[$k] = $saldo_linea;//.' / '.$linea_movimiento->cantidad;
            
            $k++;
            if ( $saldo_linea < 0 )
            {
                //return $linea_movimiento;
                return [$linea_movimiento,$vec_saldo];
            }
            $saldo_anterior = $saldo_linea;
        }

        return [null, null];
    }

    // Valida que no haya saldos negativos en moviminetos posteriores para CADA LÍNEA de registro de un documento de inventarios
    // Se usa cuando se va a anular un documento o cuando se vaya cambiar la fecha, al hacer notas creditos, entre otras transacciones, 
    // WARNING!!!! Falta la validación cuando se vaya a cambiar de fecha
    public static function validar_saldo_movimientos_posteriores_todas_lineas( $inv_doc_encabezado, $nueva_fecha, $operacion, $tipo_movimiento )
    {
        // Para anulación, todos los registros, se asume: NUEVA CANTIDAD = 0
        $lineas_documento = InvDocRegistro::where('inv_doc_encabezado_id', $inv_doc_encabezado->id)->get();
        $conta = 1;
        foreach ($lineas_documento as $linea)
        {            
            if ( $linea->item->tipo == 'servicio' )
            {
                continue;
            }

            $motivo_movimiento = $linea->motivo->movimiento;

            if ( $tipo_movimiento == 'segun_motivo' )
            {
                // Al anular se intercambian los tipos de movimientos
                if ( $operacion == 'anular' )
                {
                    if ( $motivo_movimiento == 'entrada')
                    {
                        $motivo_movimiento = 'salida';
                    }else{
                        $motivo_movimiento = 'entrada';
                    }
                }

            }

            $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores( $linea->inv_bodega_id, $linea->inv_producto_id, $inv_doc_encabezado->fecha, $linea->cantidad, 'no', $motivo_movimiento);
            
            if ( $linea_saldo_negativo[0] != null )
            {
                if ( $linea_saldo_negativo[0]->id == 0)
                {
                    return 'Saldo negativo a la fecha.'.' Producto: '.InvProducto::find($linea->inv_producto_id)->descripcion.', Saldo: '.end($linea_saldo_negativo[1]);
                }
                
                $doc_inventario = InvDocEncabezado::get_registro_impresion( $linea_saldo_negativo[0]->inv_doc_encabezado_id );
                return 'La transacción arroja saldos negativos en movimentos posteriores. Fecha: '.$doc_inventario->fecha.', Documento: '.$doc_inventario->documento_transaccion_prefijo_consecutivo.', Saldo: '.end($linea_saldo_negativo[1]);
            }

            $conta++;

        }

        return 0;
    }

    /*
        Esta función obtiene el saldo de una fecha, excluyendo o como si no hubiese existido una cantidad específica correspondiente a una línea de los movimientos de esa fecha.
    */
    public static function get_saldo_original_a_la_fecha( $producto_id, $bodega_id, $fecha, $cantidad_linea, $tipo_movimiento)
    {
        if ( $tipo_movimiento == 'entrada' )
        {
            /*
              Como es un movimiento de entrada de inventario
              Para el saldo_original_a_la_fecha de la línea a editar se usa la fórmula:
              $saldo_original_a_la_fecha = $saldo_un_dia_antes + $saldo_a_la_fecha_movimiento - $cantidad_linea_editar;

              $saldo_original_a_la_fecha: es el saldo a la fecha como si no hubiese existido la línea que se está editando.
              $saldo_a_la_fecha_movimiento: es el saldo teniendo en cuenta todas las transacciones de la fecha (incluida la cantidad de la línea a excluir, por eso se le resta)
            */
            //$saldo_un_dia_antes = InvMovimiento::get_existencia_actual( $producto_id, $bodega_id, date( "Y-m-d", strtotime($fecha."- 1 days") ) );

            //$saldo_original_a_la_fecha = $saldo_un_dia_antes + InvMovimiento::get_existencia_actual( $producto_id, $bodega_id, $fecha ) - $cantidad_linea;
              $saldo_original_a_la_fecha = $cantidad_linea - InvMovimiento::get_existencia_actual( $producto_id, $bodega_id, $fecha );
        }else{
            /*
              El valor devuelto por get_existencia_actual() ya tiene incluido la cantidad de la factura. Por tanto, como el movimiento de ventas es de salida, se le suma nuevamente la cantidad de la factura a la existencia actual para obtener el saldo_original_a_la_fecha.
              $saldo_original_a_la_fecha: es el saldo a la fecha como si no hubiese existido la línea que se está editando.
            */
            $saldo_original_a_la_fecha = InvMovimiento::get_existencia_actual( $producto_id, $bodega_id, $fecha ) + $cantidad_linea;
        }

        return $saldo_original_a_la_fecha;
    }


    // ***********   NUEVO: AGOSTO DE 2021    ***********

    public static function get_cantidad_existencia_item( $item_id, $bodega_id, $fecha_corte )
    {
        $array_wheres = [
                            [ 'core_empresa_id', '=', Auth::user()->empresa_id ],
                            [ 'inv_producto_id', '=', $item_id ],
                            [ 'inv_bodega_id', '=', $bodega_id ],
                            [ 'fecha', '<=', $fecha_corte ]
                        ];
        
        $existencia_actual = InvMovimiento::where( $array_wheres )->sum('cantidad');

        if ( is_null($existencia_actual) )
        {
            $existencia_actual = 0;
        }

        return $existencia_actual;
    }
}
