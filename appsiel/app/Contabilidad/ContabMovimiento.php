<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabMovimiento extends Model
{

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_empresa_id','core_tercero_id', 'codigo_referencia_tercero','documento_soporte','contab_cuenta_id','valor_operacion','valor_debito','valor_credito','valor_saldo','detalle_operacion','inv_producto_id','cantidad','tasa_impuesto','base_impuesto','valor_impuesto','teso_caja_id','teso_cuenta_bancaria_id','estado','creado_por','modificado_por','fecha_vencimiento','inv_bodega_id'];
    

    public $encabezado_tabla = ['Documento','Fecha','Tercero','Producto','Detalle','Cuenta','Tasa impuesto','Base impuesto','Débito','Crédito','Acción'];

    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS campo1';

        $registros = ContabMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'contab_movimientos.inv_producto_id')
                    ->leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                    ->where('contab_movimientos.core_empresa_id',Auth::user()->empresa_id)
                    ->select(DB::raw($select_raw),'contab_movimientos.fecha AS campo2','core_terceros.descripcion AS campo3',DB::raw('CONCAT(inv_productos.id," ",inv_productos.descripcion) AS campo4'),'contab_movimientos.detalle_operacion AS campo5',DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo6'),'contab_movimientos.tasa_impuesto AS campo7','contab_movimientos.base_impuesto AS campo8','contab_movimientos.valor_debito AS campo9','contab_movimientos.valor_credito AS campo10','contab_movimientos.id AS campo11')
                    ->get()
                    ->toArray();
                    /*
                    ->groupBy('contab_movimientos.core_tipo_transaccion_id')
                    ->groupBy('contab_movimientos.core_tipo_doc_app_id')
                    ->groupBy('contab_movimientos.consecutivo')
                    */

        return $registros;
    }

    public static function get_saldo_inicial($fecha_inicial, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $empresa_id )
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS tercero';
        
        $saldo_inicial_sql = ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')->leftJoin('core_terceros','core_terceros.id','=','contab_movimientos.core_tercero_id')->where('contab_movimientos.fecha','<',$fecha_inicial)
                        ->where( 'contab_cuentas.codigo', 'LIKE', $contab_cuenta_id)
                        ->where( 'core_terceros.numero_identificacion', 'LIKE', $numero_identificacion)
                        ->where( 'contab_movimientos.codigo_referencia_tercero', $operador, $codigo_referencia_tercero)
                        ->where( 'contab_movimientos.core_empresa_id', '=', $empresa_id)
                        ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ) )
                        ->get()
                        ->toArray()[0];

        return $saldo_inicial_sql['valor_saldo'];
    }

    public static function get_movimiento_cuenta($fecha_inicial, $fecha_final, $contab_cuenta_id, $numero_identificacion, $operador, $codigo_referencia_tercero, $empresa_id )
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",contab_movimientos.consecutivo) AS documento';
        
        $movimiento_cuenta = ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'contab_movimientos.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'contab_movimientos.core_tercero_id')
                    ->where('contab_movimientos.fecha','>=',$fecha_inicial)
                    ->where('contab_movimientos.fecha','<=',$fecha_final)
                    ->where('contab_cuentas.codigo','LIKE', $contab_cuenta_id )
                    ->where( 'core_terceros.numero_identificacion', 'LIKE', $numero_identificacion)
                    ->where( 'contab_movimientos.codigo_referencia_tercero', $operador, $codigo_referencia_tercero)
                    ->where( 'contab_movimientos.core_empresa_id', '=', $empresa_id)
                    ->select( 'contab_movimientos.fecha', 'contab_movimientos.detalle_operacion', DB::raw( $select_raw ), 'core_terceros.descripcion as tercero', 'contab_movimientos.valor_debito AS debito', 'contab_movimientos.valor_credito AS credito', 'contab_movimientos.codigo_referencia_tercero', 'contab_movimientos.core_tercero_id', 'contab_movimientos.core_empresa_id', 'contab_movimientos.core_tipo_transaccion_id', 'contab_movimientos.core_tipo_doc_app_id', 'contab_movimientos.consecutivo', 'contab_movimientos.documento_soporte', 'contab_movimientos.detalle_operacion')
                    ->orderBy('fecha')
                    ->get()
                    ->toArray();

        return $movimiento_cuenta;
    }

    public static function get_movimiento_arbol_grupo_cuenta($empresa_id, $fecha_inicial, $fecha_final, $grupo_abuelo_id )
    {
        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
            ->leftJoin('contab_arbol_grupos_cuentas','contab_arbol_grupos_cuentas.hijo_id','=','contab_cuentas.contab_cuenta_grupo_id')
            ->where('contab_movimientos.core_empresa_id',$empresa_id)
            ->where('contab_movimientos.fecha','<=',$fecha_final)
            ->where('contab_arbol_grupos_cuentas.abuelo_id',$grupo_abuelo_id)
            ->groupBy('contab_movimientos.contab_cuenta_id')
            ->selectRaw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo, contab_arbol_grupos_cuentas.abuelo_descripcion, contab_arbol_grupos_cuentas.padre_descripcion, contab_arbol_grupos_cuentas.hijo_descripcion, contab_arbol_grupos_cuentas.abuelo_id, contab_arbol_grupos_cuentas.padre_id, contab_arbol_grupos_cuentas.hijo_id, contab_cuentas.descripcion AS cuenta_descripcion, contab_cuentas.id AS cuenta_id' )
            ->get()->toArray();
    }

    public static function get_registros_contables($core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo )
    {
        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
            ->where('contab_movimientos.core_tipo_transaccion_id',$core_tipo_transaccion_id)
            ->where('contab_movimientos.core_tipo_transaccion_id',$core_tipo_transaccion_id)
            ->where('contab_movimientos.consecutivo',$consecutivo)
            ->groupBy('contab_movimientos.contab_cuenta_id')
            ->selectRaw( 'contab_cuentas.descripcion AS cuenta_descripcion, contab_cuentas.codigo AS cuenta_codigo, sum(contab_movimientos.valor_debito) AS valor_debito, sum(contab_movimientos.valor_credito) AS valor_credito' )
            ->get()->toArray();
    }

    public static function get_movimiento_impuestos( $vec_tipos_transaccion_ids, $fecha_inicial, $fecha_final, $nivel_detalle )
    {
        /**/
        switch ( $nivel_detalle )
        {
            case 'ninguno':
                $nivel_detalle = ['contab_impuestos.id'];
                
                break;

            case 'cuentas':
                $nivel_detalle = ['contab_impuestos.id','contab_movimientos.contab_cuenta_id'];
                
                break;

            case 'productos':
                $nivel_detalle = ['contab_impuestos.id','inv_productos.id'];
                
                break;
            
            default:
                # code...
                break;
        }
        

        return ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                    ->leftJoin('sys_tipos_transacciones','sys_tipos_transacciones.id','=','contab_movimientos.core_tipo_transaccion_id')
                    ->leftJoin('inv_productos','inv_productos.id','=','contab_movimientos.inv_producto_id')
                    ->leftJoin('contab_impuestos','contab_impuestos.id','=','inv_productos.impuesto_id')
                    ->whereIn('contab_movimientos.core_tipo_transaccion_id',$vec_tipos_transaccion_ids)
                    ->where('contab_movimientos.fecha','>=',$fecha_inicial)
                    ->where('contab_movimientos.fecha','<=',$fecha_final)
                    ->groupBy( $nivel_detalle )
                    ->selectRaw( 
                                'sys_tipos_transacciones.descripcion AS transaccion_descripcion,
                                contab_impuestos.descripcion AS impuesto_descripcion,
                                contab_impuestos.tasa_impuesto AS impuesto_tasa,
                                contab_cuentas.descripcion AS cuenta_descripcion,
                                contab_cuentas.codigo AS cuenta_codigo,
                                inv_productos.descripcion AS producto_descripcion,
                                inv_productos.unidad_medida1 AS producto_unidad_medida,
                                contab_movimientos.tasa_impuesto AS movimiento_tasa,
                                sum(contab_movimientos.valor_debito) AS valor_debito,
                                sum(contab_movimientos.valor_credito) AS valor_credito' )
                    ->orderBy( 'sys_tipos_transacciones.id' )
                    ->orderBy( 'contab_impuestos.id' )
                    ->get();
    }
}