<?php 

namespace App\VentasPos\Services;

use App\Contabilidad\ContabMovimiento;
use App\VentasPos\DocRegistro;
use App\VentasPos\FacturaPos;

class AccountingServices
{    
    public function contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cta_bancaria_id = 0 )
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id]  + 
                            [ 'teso_cta_bancaria_id' => $teso_cta_bancaria_id] 
                        );
    }

    

    // Recontabilizar un documento dada su ID
    public function recontabilizar_factura( $documento_id )
    {
        $documento = FacturaPos::find($documento_id);

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
                        ->where('consecutivo', $documento->consecutivo)
                        ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = DocRegistro::where('vtas_pos_doc_encabezado_id', $documento->id)->get();

        $total_documento = 0;
        $n = 1;
        $obj_sales_serv = new SalesServices();
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. ' . $linea->descripcion;
            $obj_sales_serv->contabilizar_movimiento_credito( $documento->toArray() + $linea->toArray(), $detalle_operacion);
            $total_documento += $linea->precio_total;
            $n++;
        }

        $forma_pago = $documento->forma_pago;

        $datos = $documento->toArray();
        $obj_sales_serv->contabilizar_movimiento_debito( $forma_pago, $datos, $datos['valor_total'], $detalle_operacion, $documento->pdv->caja_default_id);

        return true;
    }
        
}