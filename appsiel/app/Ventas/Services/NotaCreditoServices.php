<?php 

namespace App\Ventas\Services;

use App\CxC\CxcAbono;
use App\CxC\CxcMovimiento;
use App\Inventarios\InvProducto;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\Cliente;

class NotaCreditoServices
{    
    public function contabilizar_movimiento_debito( $datos, $detalle_operacion )
    {
        $accounting_service = new AccountingServices();

        // IVA descontable (DB)
        // Si se ha liquidado impuestos en la transacción
        if ( isset( $datos['tasa_impuesto'] ) && $datos['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_devolucion_ventas( $datos['inv_producto_id'] );
            $accounting_service->contabilizar_registro( $datos, $cta_impuesto_ventas_id, $detalle_operacion, abs( $datos['valor_impuesto'] ), 0);
        }

        // La cuenta de ingresos se toma del grupo de inventarios
        $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $datos['inv_producto_id'] );
        $accounting_service->contabilizar_registro( $datos, $cta_ingresos_id, $detalle_operacion, $datos['base_impuesto_total'], 0);
    }

    public function contabilizar_movimiento_credito( $datos, $total_documento, $detalle_operacion, $factura = null )
    {
        $forma_pago = '';
        if ($factura != null) {
            $forma_pago = $factura->forma_pago;
        }
            
        // Se resetean estos campos del registro
        $datos['inv_producto_id'] = 0;
        $datos['cantidad '] = 0;
        $datos['tasa_impuesto'] = 0;
        $datos['base_impuesto'] = 0;
        $datos['valor_impuesto'] = 0;
        $datos['inv_bodega_id'] = 0;
        
        if ( $forma_pago == 'credito')
        {
            if ( is_null($factura) )
            {
                $contab_cuenta_id = Cliente::get_cuenta_cartera( $datos['cliente_id'] );
            }else{
                $contab_cuenta_id = Cliente::get_cuenta_cartera( $factura->cliente_id );
            }

        }        
        
        // Agregar el movimiento a tesorería
        if ( $forma_pago == 'contado')
        {
            $movimiento_teso = TesoMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

            if ($movimiento_teso->caja != null)
            {
                $datos['teso_caja_id'] = $movimiento_teso->caja->id;
                $contab_cuenta_id = $movimiento_teso->caja->contab_cuenta_id;
            }
            
            if ($movimiento_teso->cuenta_bancaria != null)
            {
                $datos['teso_cuenta_bancaria_id'] = $movimiento_teso->cuenta_bancaria->id;
                $contab_cuenta_id = $movimiento_teso->cuenta_bancaria->contab_cuenta_id;
            }   
        }
        
        (new AccountingServices())->contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, 0, abs($total_documento) );
    }

    /**
     * Para factura credito
     */
    public function actualizar_registro_pago( $total_nota, $factura, $nota, $accion )
    {
        /*
            Al crear la nota: Se disminuye el saldo pendiente y se aumenta el valor pagado
            A anular la nota: Se aumenta el saldo pendiente y se disminuye el valor pagado
        */

        // total_nota es negativo cuando se hace la nota y positivo cuando se anula

        $movimiento_cxc = CxcMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

        if ( $movimiento_cxc == null ) {
            return null;
        }
        
        $nuevo_total_pendiente = $movimiento_cxc->saldo_pendiente + $total_nota; 
        $nuevo_total_pagado = $movimiento_cxc->valor_pagado - $total_nota;

        $estado = 'Pendiente';
        if ( $nuevo_total_pendiente == 0)
        {
            $estado = 'Pagado';
        }

        $movimiento_cxc->update( [ 
                                    'valor_pagado' => $nuevo_total_pagado,
                                    'saldo_pendiente' => $nuevo_total_pendiente,
                                    'estado' => $estado
                                ] );

        $datos = ['core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id]+
                  ['core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id]+
                  ['consecutivo' => $nota->consecutivo]+
                  ['fecha' => $nota->fecha]+
                  ['core_empresa_id' => $nota->core_empresa_id]+
                  ['core_tercero_id' => $nota->core_tercero_id]+
                  ['modelo_referencia_tercero_index' => 'App\Ventas\Cliente']+
                  ['referencia_tercero_id' => $factura->cliente_id]+
                  ['doc_cxc_transacc_id' => $factura->core_tipo_transaccion_id]+
                  ['doc_cxc_tipo_doc_id' => $factura->core_tipo_doc_app_id]+
                  ['doc_cxc_consecutivo' => $factura->consecutivo]+
                  ['doc_cruce_transacc_id' => 0]+
                  ['doc_cruce_tipo_doc_id' => 0]+
                  ['doc_cruce_consecutivo' => 0]+
                  ['abono' => abs($total_nota)]+
                  ['creado_por' => $nota->creado_por];

        if ( $accion == 'crear')
        {
            // Almacenar registro de abono
            CxcAbono::create( $datos );
        }else{
            // Eliminar registro de abono
            CxcAbono::where( $datos )->delete();
        }
    }

    public function actualizar_movimiento_tesoreria( $total_nota, $factura, $nota, $accion )
    {
        /*
            Al crear la nota: Se crea un nuevo registro de tesorería
            A anular la nota: Se elimina el movimiento de tesorería relacionado
        */

        $datos = ['core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id]+
                  ['core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id]+
                  ['consecutivo' => $nota->consecutivo]+
                  ['fecha' => $nota->fecha]+
                  ['core_empresa_id' => $nota->core_empresa_id]+
                  ['core_tercero_id' => $nota->core_tercero_id]+
                  ['creado_por' => $nota->creado_por];

        if ( $accion == 'crear')
        {
            $movimiento_teso = TesoMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

            if ($movimiento_teso == null) {
                $registros_medio_pago['teso_caja_id'] = 1;
                $registros_medio_pago['teso_cuenta_bancaria_id'] = 0;
                $registros_medio_pago['teso_medio_recaudo_id'] = 1;
            }else{
                $registros_medio_pago['teso_caja_id'] = $movimiento_teso->teso_caja_id;
                $registros_medio_pago['teso_cuenta_bancaria_id'] = $movimiento_teso->teso_cuenta_bancaria_id;
                $registros_medio_pago['teso_medio_recaudo_id'] = $movimiento_teso->teso_medio_recaudo_id;
            }
            $registros_medio_pago['teso_motivo_id'] = (int)config('tesoreria.motivo_devolucion_ventas_id');
            $registros_medio_pago['valor_recaudo'] = abs($total_nota);            

            $obj_teso_movim = new TesoMovimiento();
            $obj_teso_movim->almacenar_registro_pago_contado( $datos, $registros_medio_pago, 'salida', abs($total_nota) );

        }else{

            $movimiento_teso = TesoMovimiento::where('core_tipo_transaccion_id', $nota->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $nota->core_tipo_doc_app_id)
                                ->where('consecutivo', $nota->consecutivo)
                                ->get()
                                ->first();

            if ( $movimiento_teso != null ) {
                $movimiento_teso->delete();
            }
        }
    }
        
}