<?php 

namespace App\Inventarios\Services;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvProducto;

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

    public function recontabilizar_documento($documento_id)
    {
        $documento = InvDocEncabezado::find( $documento_id );

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id',$documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$documento->core_tipo_doc_app_id)
                        ->where('consecutivo',$documento->consecutivo)
                        ->delete();        

        // Obtener líneas de registros del documento
        $registros_documento = InvDocRegistro::where( 'inv_doc_encabezado_id', $documento->id )->get();

        foreach ($registros_documento as $linea)
        {
            $motivo = InvMotivo::find( $linea->inv_motivo_id );

            $detalle_operacion = 'Recontabilizado. '.$linea->descripcion;

            // Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'entrada')
            {
                // Inventarios (DB)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                $this->contabilizar_registro( $documento->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, abs($linea->costo_total), 0);
                
                // Cta. Contrapartida (CR)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                $this->contabilizar_registro( $documento->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, 0, abs($linea->costo_total) );
            }

            // Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'salida')
            {
                // Inventarios (CR)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                $this->contabilizar_registro( $documento->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, 0, abs($linea->costo_total));
                
                // Cta. Contrapartida (DB)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                $this->contabilizar_registro( $documento->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, abs($linea->costo_total), 0 );
            }
                
        }
    }
        
}