<?php

namespace App\Compras\Services;

use App\Compras\DescuentoProntoPago;
use App\Contabilidad\ContabMovimiento;
use App\CxP\CxpAbono;
use App\CxP\CxpMovimiento;
use App\Inventarios\InvDocEncabezado;
use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMotivo;

class TesoreriaService
{
    public function get_campo_lineas_recaudos($lineas_registros_medios_recaudo, $lineas_registros_originales){

        $lineas_registros_medios_recaudos = json_decode( $lineas_registros_medios_recaudo, true );

        $total_documento = (new EncabezadoDocumentoService())->get_total_documento_desde_lineas_registros( $lineas_registros_originales );

        if ( count($lineas_registros_medios_recaudos) <= 1 )
        {
            $teso_motivo_id = '1-Recaudo clientes';
            $teso_motivo = TesoMotivo::find((int)config('tesoreria.motivo_tesoreria_compras_contado'));
            if ($teso_motivo != null) {
                $teso_motivo_id = $teso_motivo->id . '-' . $teso_motivo->descripcion;
            }

            $teso_caja_id = '1-Caja general';
            $caja = TesoCaja::find((int)config('tesoreria.caja_default_id'));
            if ($caja != null) {
                $teso_caja_id = $caja->id . '-' . $caja->descripcion;
            }

            $lineas_registros_medios_recaudos = [[
                'teso_medio_recaudo_id' => '1-Efectivo',
                'teso_motivo_id' => $teso_motivo_id,
                'teso_caja_id' => $teso_caja_id,
                'teso_cuenta_bancaria_id' => '0-',
                'valor' => '$' . $total_documento
            ]];

            return json_decode( json_encode( $lineas_registros_medios_recaudos ) );
        }

        return (new RegistrosMediosPago())->depurar_tabla_registros_medios_recaudos( $lineas_registros_medios_recaudo, $total_documento );
    }

    public function get_items_compras_con_descuentos_por_pronto_pago($fecha_desde, $fecha_hasta)
    {
        $movimiento_contab_descuentos_compras = $this->get_resgitros_descuentos_compras($fecha_desde, $fecha_hasta);

        $arr_consecutivos_documentos_pagos = $movimiento_contab_descuentos_compras->pluck('consecutivo')
                                            ->toArray();

        $abonos_a_facturas_compras = CxpAbono::where([
                                                ['core_tipo_transaccion_id','=',33]
                                            ])
                                            ->whereIn('consecutivo',$arr_consecutivos_documentos_pagos)
                                            ->get();
        
        $items_con_descuentos = [];

        $aux = [];
        foreach ($abonos_a_facturas_compras as $abono) {

            $valor_descuento_total = abs($movimiento_contab_descuentos_compras->where('consecutivo',$abono->consecutivo)->sum('valor_saldo'));

            $factura_compras = $abono->get_encabezado_documento_cxp();

            $entrada_almacen = InvDocEncabezado::find( $factura_compras->entrada_almacen_id ); 

            $lineas_registros_entrada = $entrada_almacen->lineas_registros;

            $valor_total_entrada = abs($lineas_registros_entrada->sum('costo_total'));

            foreach ($lineas_registros_entrada as $linea_registro_entrada) {

                $porcentaje_participacion_item = abs( $linea_registro_entrada->costo_total / $valor_total_entrada );

                $aux[] = [
                    'abono_consecutivo' => $abono->consecutivo,
                    'item' => $linea_registro_entrada->item->descripcion,
                    'consecutivo_entrada_almacen' => $entrada_almacen->consecutivo,
                    'porcentaje_participacion_item' => $porcentaje_participacion_item,
                    'valor_descuento' => $valor_descuento_total * $porcentaje_participacion_item
                ];

                if (!isset($items_con_descuentos[$linea_registro_entrada->inv_producto_id])) {
                    $items_con_descuentos[$linea_registro_entrada->inv_producto_id] = $valor_descuento_total * $porcentaje_participacion_item;
                } else {
                    $items_con_descuentos[$linea_registro_entrada->inv_producto_id] += $valor_descuento_total * $porcentaje_participacion_item;
                }
            }           
        }

        return $items_con_descuentos;
    }

    public function get_resgitros_descuentos_compras($fecha_desde, $fecha_hasta)
    {
        $cuentas_descuentos_ids = DescuentoProntoPago::get()->pluck('contab_cuenta_id')->toArray();

        // 33 = Pagos de CxP
        return ContabMovimiento::where('core_tipo_transaccion_id', 33)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->whereIn('contab_cuenta_id', $cuentas_descuentos_ids )
                            ->get();
    }

    public function get_lineas_items_compras_con_descuentos_por_pronto_pago($fecha_desde, $fecha_hasta, $detalla_documentos)
    {
        $movimiento_contab_descuentos_compras = $this->get_resgitros_descuentos_compras($fecha_desde, $fecha_hasta);

        $arr_consecutivos_documentos_pagos = $movimiento_contab_descuentos_compras->pluck('consecutivo')
                                            ->toArray();

        $abonos_a_facturas_compras = CxpAbono::where([
                                                ['core_tipo_transaccion_id','=',33]
                                            ])
                                            ->whereIn('consecutivo',$arr_consecutivos_documentos_pagos)
                                            ->get();
        
        $items_con_descuentos = [];

        $lineas_detalladas = [];
        foreach ($abonos_a_facturas_compras as $abono) {

            $valor_descuento_total = abs($movimiento_contab_descuentos_compras->where('consecutivo',$abono->consecutivo)->sum('valor_saldo'));

            $factura_compras = $abono->get_encabezado_documento_cxp();

            $entrada_almacen = InvDocEncabezado::find( $factura_compras->entrada_almacen_id ); 

            $lineas_registros_entrada = $entrada_almacen->lineas_registros;

            $valor_total_entrada = abs($lineas_registros_entrada->sum('costo_total'));

            foreach ($lineas_registros_entrada as $linea_registro_entrada) {

                $porcentaje_participacion_item = abs( $linea_registro_entrada->costo_total / $valor_total_entrada );

                $lineas_detalladas[] = (object)[
                    'item' => $linea_registro_entrada->inv_producto_id . ' - ' . $linea_registro_entrada->item->descripcion,
                    'doc_pago' => $abono->get_label_documento(),
                    'factura_compras' => $factura_compras->get_label_documento(),
                    'total_factura_compras' => $factura_compras->valor_total,
                    'porcentaje_participacion_item' => round($porcentaje_participacion_item * 100, 2) . '%',
                    'valor_descuento' => $valor_descuento_total * $porcentaje_participacion_item
                ];

                if (!isset($items_con_descuentos[$linea_registro_entrada->inv_producto_id])) {

                    $items_con_descuentos[$linea_registro_entrada->inv_producto_id]['item'] = $linea_registro_entrada->inv_producto_id . ' - ' . $linea_registro_entrada->item->descripcion;

                    $items_con_descuentos[$linea_registro_entrada->inv_producto_id]['valor_descuento'] = $valor_descuento_total * $porcentaje_participacion_item;
                } else {
                    $items_con_descuentos[$linea_registro_entrada->inv_producto_id]['valor_descuento'] += $valor_descuento_total * $porcentaje_participacion_item;
                }
            }           
        }

        if ($detalla_documentos) {
            return $lineas_detalladas;
        }

        return $items_con_descuentos;
    }
}

