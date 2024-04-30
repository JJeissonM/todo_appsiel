<?php 

namespace App\Inventarios\Services;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use Illuminate\Support\Facades\Auth;

class InvDocumentsLinesService
{	
    public function preparar_array_lineas_registros( $bodega_id, $request_registros, $modo_ajuste )
    {
        $lineas_registros = json_decode( $request_registros );

        // Quitar primera línea
        array_shift( $lineas_registros );

        // Quitar las dos últimas líneas
        array_pop($lineas_registros);
        array_pop($lineas_registros);

        $cantidad = count($lineas_registros);
        for ($i = 0; $i < $cantidad; $i++)
        {
            $lineas_registros[$i]->inv_bodega_id = $bodega_id;
            $lineas_registros[$i]->inv_producto_id = $lineas_registros[$i]->inv_producto_id;
            $lineas_registros[$i]->inv_motivo_id = explode( "-", $lineas_registros[$i]->motivo )[0];
            $lineas_registros[$i]->costo_unitario = (float) substr($lineas_registros[$i]->costo_unitario, 1);
            $lineas_registros[$i]->cantidad = (float) substr($lineas_registros[$i]->cantidad, 0, strpos($lineas_registros[$i]->cantidad, " "));
            $lineas_registros[$i]->costo_total = (float) substr($lineas_registros[$i]->costo_total, 1);

            if (!is_null($modo_ajuste))
            {
                if ($modo_ajuste == 'solo_cantidad')
                {
                    $lineas_registros[$i]->costo_unitario = 0;
                    $lineas_registros[$i]->costo_total = 0;
                }
            }
        }

        return $lineas_registros;
    }

    public function update_document_line( InvDocRegistro $linea_registro, $costo_unitario, $cantidad )
    {
        $doc_encabezado = $linea_registro->encabezado_documento;

        $cantidad = abs($cantidad);
        if ($linea_registro->motivo->movimiento == 'salida') {
            $cantidad = $cantidad * -1;
        }

        $costo_total = $costo_unitario * $cantidad;

        if ( $linea_registro->item->tipo == 'producto')
        {
            // 1. Actualiza movimiento de inventarios
            InvMovimiento::where([
                ['core_tipo_transaccion_id', '=', $doc_encabezado->core_tipo_transaccion_id],
                ['inv_movimientos.core_empresa_id', '=', Auth::user()->empresa_id],
                ['core_tipo_doc_app_id', '=', $doc_encabezado->core_tipo_doc_app_id],
                ['consecutivo', '=', $doc_encabezado->consecutivo],
                ['inv_producto_id', '=', $linea_registro->inv_producto_id],
                ['cantidad', '=', $linea_registro->cantidad]
            ])
            ->update([
                    'costo_unitario' => $costo_unitario,
                    'cantidad' => $cantidad,
                    'costo_total' => $costo_total
                ]);

            // 2. Si es un motivo de entrada, se calcula el costo promedio
            if ($linea_registro->motivo->movimiento == 'entrada') {
                // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request

                $average_cost_serv = new AverageCost();
                $costo_prom = $average_cost_serv->calculate_average_cost($linea_registro->inv_bodega_id, $linea_registro->inv_producto_id, $costo_unitario, $doc_encabezado->fecha, $cantidad);
                
                $this->actualizar_costo_promedio($linea_registro->inv_bodega_id, $linea_registro->inv_producto_id, $costo_prom, $doc_encabezado->core_tipo_transaccion_id, $average_cost_serv);

                // Si el motivo es de entrada SE DEBITA EL INVENTARIO
                // 3. Actualizar movimiento contable del registro del documento de inventario
                // Inventarios (DB)
                $cta_inventarios_id = $linea_registro->item->get_cuenta_inventarios($linea_registro->inv_producto_id);
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_inventarios_id)
                    ->update([
                        'valor_debito' => abs($costo_total),
                        'valor_saldo' => abs($costo_total),
                        'cantidad' => $cantidad
                    ]);

                // Cta. Contrapartida (CR) Dada por el motivo de inventarios de la transaccion 
                // Motivos de inventarios y ventas: Costo de ventas
                // Moivos de compras: Cuentas por legalizar
                $cta_contrapartida_id = $linea_registro->motivo->cta_contrapartida_id;
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_contrapartida_id)
                    ->update([
                        'valor_credito' => abs($costo_total) * -1,
                        'valor_saldo' => abs($costo_total) * -1,
                        'cantidad' => $cantidad
                    ]);
            } else {

                // Si el motivo es de SALIDA se ACREDITA EL INVENTARIO
                // 3. Actualizar movimiento contable del registro del documento de inventario
                // Inventarios (CR)
                $cta_inventarios_id = $linea_registro->item->get_cuenta_inventarios($linea_registro->inv_producto_id);
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_inventarios_id)
                    ->update([
                        'valor_credito' => abs($costo_total) * -1,
                        'valor_saldo' => abs($costo_total) * -1,
                        'cantidad' => $cantidad
                    ]);

                // Cta. Contrapartida (DB) Dada por el motivo de inventarios de la transaccion 
                // Motivos de inventarios y ventas: Costo de ventas
                // Moivos de compras: Cuentas por legalizar
                $cta_contrapartida_id = $linea_registro->motivo->cta_contrapartida_id;
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_contrapartida_id)
                    ->update([
                        'valor_debito' => abs($costo_total),
                        'valor_saldo' => abs($costo_total),
                        'cantidad' => $cantidad
                    ]);
            }
        } // Fin Si es producto

        // 4. Actualizar el registro del documento de factura
        $linea_registro->update([
            'costo_unitario' => $costo_unitario,
            'cantidad' => $cantidad,
            'costo_total' => $costo_total
        ]);

        return $linea_registro;
    }

    public function actualizar_costo_promedio($inv_bodega_id, $inv_producto_id, $costo_prom, $core_tipo_transaccion_id, $average_cost_serv)
    {
        $tipo_transferencia = 2;
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1  )
        {
            // Actualizo/Almaceno el costo promedio
            $average_cost_serv->set_costo_promedio( $inv_bodega_id, $inv_producto_id, $costo_prom);
        }else{

            // Cuando no maneja costo promedio por bodegas (un solo costo para todo)

            // Solo se calcula costo promedio, si la entrada NO es por transferencia
            if ($core_tipo_transaccion_id != $tipo_transferencia) 
            {
                // Actualizo/Almaceno el costo promedio
                $average_cost_serv->set_costo_promedio( $inv_bodega_id, $inv_producto_id, $costo_prom);
            }
        }
    }
}