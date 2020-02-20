<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Inventarios\InvMotivo;

class InvMovimiento extends Model
{
    //protected $table = 'inv_movimientos';

    protected $fillable = ['core_empresa_id','inv_doc_encabezado_id','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','inv_motivo_id','inv_bodega_id','inv_producto_id','costo_unitario','cantidad','costo_total','creado_por','modificado_por','codigo_referencia_tercero','core_tercero_id'];

    public $encabezado_tabla = ['Fecha','Documento','Producto','Bodega','Motivo','Movimiento','Costo unit.','Cantidad','Costo total','ID','Acción'];

    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_movimientos.consecutivo) AS campo2';

        $registros = InvMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_movimientos.core_tipo_doc_app_id')
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_movimientos.inv_producto_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_movimientos.inv_bodega_id')
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'inv_movimientos.inv_motivo_id')
                    ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->select(
                                'inv_movimientos.fecha AS campo1',
                                DB::raw($select_raw),
                                DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS campo3'),
                                'inv_bodegas.descripcion AS campo4',
                                'inv_motivos.descripcion AS campo5',
                                'inv_motivos.movimiento AS campo6',
                                'inv_movimientos.costo_unitario AS campo7',
                                'inv_movimientos.cantidad AS campo8',
                                'inv_movimientos.costo_total AS campo9',
                                'inv_movimientos.id AS campo10',
                                'inv_movimientos.id AS campo11')
                    ->get()
                    ->toArray();

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
                                //->leftJoin('inv_costo_prom_productos','inv_costo_prom_productos.inv_producto_id','=','inv_movimientos.inv_producto_id')
                                ->where('inv_doc_encabezados.fecha','<=',$fecha_corte)
                                ->where('inv_movimientos.inv_bodega_id',$operador1,$mov_bodega_id)
                                ->where('inv_grupos.id',$operador2,$grupo_inventario_id)
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.descripcion',
                                            'inv_productos.unidad_medida1',
                                            'inv_bodegas.descripcion AS bodega',
                                            DB::raw('sum(inv_movimientos.cantidad) as Cantidad'),
                                            DB::raw('sum(inv_movimientos.costo_total) as Costo') )
                                ->groupBy($orden)
                                ->get()
                                ->toArray();//,'inv_costo_prom_productos.costo_promedio as costo_promedio_ponderado'
                                //dd( [$fecha_corte, $productos] );
        return $productos;
    }

    public static function get_existencia_corte( $array_wheres )
    {
        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                                ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                                ->leftJoin('inv_grupos','inv_grupos.id','=','inv_productos.inv_grupo_id')
                                ->leftJoin('inv_bodegas','inv_bodegas.id','=','inv_doc_encabezados.inv_bodega_id')
                                ->where( $array_wheres )
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.descripcion',
                                            'inv_productos.unidad_medida1',
                                            'inv_productos.unidad_medida2',
                                            'inv_bodegas.descripcion AS bodega',
                                            DB::raw('sum(inv_movimientos.cantidad) as Cantidad'),
                                            DB::raw('sum(inv_movimientos.costo_total) as Costo') )
                                ->groupBy('inv_productos.descripcion')
                                ->groupBy('inv_productos.unidad_medida2')
                                ->get()
                                ->toArray();
    }

    public function producto()
    {
        return $this->belongsTo('App\Inventarios\InvProducto');
    }

    public static function get_saldo_inicial($id_producto, $id_bodega, $fecha_inicial )
    {
        $sql_saldo_inicial = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                    ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                    ->where('inv_productos.id','=',$id_producto)
                    ->where('inv_movimientos.inv_bodega_id','=', $id_bodega)
                    ->where('inv_doc_encabezados.fecha','<',$fecha_inicial)
                    ->select(DB::raw('sum(inv_movimientos.cantidad) as mCantidad'),DB::raw('sum(inv_movimientos.costo_total) as mCosto'))
                    ->get()
                    ->toArray();    
        return $sql_saldo_inicial[0];
    }

    public static function get_movimiento($id_producto, $id_bodega, $fecha_inicial, $fecha_final )
    {
        //$select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2';

        return InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                                ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                                ->leftJoin('inv_motivos','inv_motivos.id','=','inv_movimientos.inv_motivo_id')
                                ->where('inv_movimientos.inv_bodega_id','=',$id_bodega)
                                ->where('inv_movimientos.inv_producto_id','=',$id_producto)
                                ->whereBetween('inv_doc_encabezados.fecha', [$fecha_inicial, $fecha_final])
                                ->select('inv_doc_encabezados.id',
                                        'inv_doc_encabezados.fecha',
                                        'inv_motivos.movimiento',
                                        'inv_movimientos.inv_doc_encabezado_id',
                                        'inv_movimientos.cantidad',
                                        'inv_movimientos.costo_unitario',
                                        'inv_movimientos.costo_total',
                                        'inv_movimientos.core_tipo_transaccion_id')
                                ->orderBy('inv_movimientos.fecha')
                                ->orderBy('inv_movimientos.created_at')
                                ->get();
    }

    public static function get_existencia_producto( $producto_id, $bodega_id, $fecha_corte )
    {
        return InvMovimiento::leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                    ->where('inv_doc_encabezados.fecha','<=',$fecha_corte)
                    ->where('inv_movimientos.inv_bodega_id',$bodega_id)
                    ->where('inv_movimientos.inv_producto_id',$producto_id)
                    ->select( DB::raw('sum(inv_movimientos.cantidad) as Cantidad'),DB::raw('sum(inv_movimientos.costo_total) as Costo'))
                    ->get()[0];
    }

    public static function get_existencia_actual( $producto_id, $bodega_id, $fecha_corte )
    {
        $existencia_actual = InvMovimiento::where('inv_bodega_id','=',$bodega_id)
                                ->where('inv_producto_id','=',$producto_id)
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
            
            /*if ($k==1) {
                dd( [ 'k: '.$k, 'saldo_linea: '.$saldo_linea, 'saldo_anterior: '.$saldo_anterior, $linea_movimiento->cantidad ] );
            }*/
            $k++;
            if ( $saldo_linea < 0 )
            {
                //return $linea_movimiento;
                return [$linea_movimiento,$vec_saldo];
            }
            $saldo_anterior = $saldo_linea;
        }

        //dd($vec_saldo);
        return [null,null];
    }

    // Valida que no haya saldos negativos en moviminetos posteriores para TODOS los registros de un documento de inventarios
    // Se usa cuando se va a anular un documento o cuando se vaya cambiar la fecha, al hacer notas creditos, entre otras transacciones, 
    // WARNING!!!! Falta la validación cuando se vaya a cambiar de fecha
    public static function validar_saldo_movimientos_posteriores_todas_lineas( $inv_doc_encabezado, $nueva_fecha, $operacion, $tipo_movimiento )
    {
        // Para anulación, todos los registros: NUEVA CANTIDAD = 0
        $lineas_documento = InvDocRegistro::where('inv_doc_encabezado_id', $inv_doc_encabezado->id)->get();
        $conta = 1;
        foreach ($lineas_documento as $linea)
        {
            if ( $tipo_movimiento == 'segun_motivo' )
            {
                $motivo = InvMotivo::find($linea->inv_motivo_id);
                $tipo_movimiento = $motivo->movimiento;
                // Al anular se intercambian los tipos de movimientos
                if ( $operacion == 'anular' )
                {
                    if ( $motivo->movimiento == 'entrada')
                    {
                        $tipo_movimiento = 'salida';
                    }else{
                        $tipo_movimiento = 'entrada';
                    }
                }
            }

            $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores( $linea->inv_bodega_id, $linea->inv_producto_id, $inv_doc_encabezado->fecha, $linea->cantidad, 'no', $tipo_movimiento);
            
            if ( !is_null($linea_saldo_negativo[0]) )
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
}