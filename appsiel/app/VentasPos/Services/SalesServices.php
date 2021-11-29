<?php 

namespace App\VentasPos\Services;

use App\VentasPos\Services\AccountingServices;

/**
        * !!!!! No se deberia acceder directamente a los modelos de otras aplicaciones
 */
use App\Inventarios\InvProducto;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Ventas\Cliente;

class SalesServices
{
    // Contabilizar Ingresos de ventas e Impuestos
    public function contabilizar_movimiento_credito( $data_invoice_line, $detalle_operacion )
    {
        $obj_accou_serv = new AccountingServices();
        
        // IVA generado (CR)
        // Si se ha liquidado impuestos en la transacción
        $valor_total_impuesto = 0;
        if ( $data_invoice_line['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_ventas( $data_invoice_line['inv_producto_id'] );
            $valor_total_impuesto = abs( $data_invoice_line['valor_impuesto'] * $data_invoice_line['cantidad'] );

            $obj_accou_serv->contabilizar_registro( $data_invoice_line, $cta_impuesto_ventas_id, $detalle_operacion, 0, abs($valor_total_impuesto) );
        }

        // Contabilizar Ingresos (CR)
        // La cuenta de ingresos se toma del grupo de inventarios
        $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $data_invoice_line['inv_producto_id'] );
        $obj_accou_serv->contabilizar_registro( $data_invoice_line, $cta_ingresos_id, $detalle_operacion, 0, $data_invoice_line['base_impuesto_total']);
    }    

    public function contabilizar_movimiento_debito( $forma_pago, $datos, $total_documento, $detalle_operacion, $caja_banco_id = null)
    {
        
        $obj_accou_serv = new AccountingServices();

        if ($forma_pago == 'credito') {
            // Se resetean estos campos del registro
            $datos['inv_producto_id'] = 0;
            $datos['cantidad '] = 0;
            $datos['tasa_impuesto'] = 0;
            $datos['base_impuesto'] = 0;
            $datos['valor_impuesto'] = 0;
            $datos['inv_bodega_id'] = 0;

            // La cuenta de CARTERA se toma de la clase del cliente
            $cta_x_cobrar_id = Cliente::get_cuenta_cartera($datos['cliente_id']);
            $obj_accou_serv->contabilizar_registro($datos, $cta_x_cobrar_id, $detalle_operacion, $total_documento, 0);
        }

        // Contabiliazar el movimiento de tesorería
        if ($forma_pago == 'contado')
        {
            $lineas_recaudos = json_decode($datos['lineas_registros_medios_recaudos']);

            if (!is_null($lineas_recaudos)) //&& $datos['lineas_registros_medios_recaudos'] != '' )
            {
                foreach ($lineas_recaudos as $linea)
                {
                    $contab_cuenta_id = TesoCaja::find(1)->contab_cuenta_id;

                    $teso_caja_id = explode("-", $linea->teso_caja_id)[0];
                    if ($teso_caja_id != 0) {
                        $contab_cuenta_id = TesoCaja::find($teso_caja_id)->contab_cuenta_id;
                    }

                    $teso_cuenta_bancaria_id = explode("-", $linea->teso_cuenta_bancaria_id)[0];
                    if ($teso_cuenta_bancaria_id != 0) {
                        $contab_cuenta_id = TesoCuentaBancaria::find($teso_cuenta_bancaria_id)->contab_cuenta_id;
                    }

                    $obj_accou_serv->contabilizar_registro($datos, $contab_cuenta_id, $detalle_operacion, (float)substr($linea->valor, 1), 0, $teso_caja_id, $teso_cuenta_bancaria_id);
                }
            }
        }
    }
        
}