<?php 

namespace App\VentasPos\Services;

use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoEntidadFinanciera;
use App\Tesoreria\TesoMovimiento;
use App\VentasPos\FacturaPos;
use App\VentasPos\Movimiento;
use App\VentasPos\Pdv;

class ReportsServices
{    
    public function resumen_ventas_arqueo_caja($fecha, $teso_caja_id)
    {
        $pdv = Pdv::where('caja_default_id',$teso_caja_id)->get()->first();

        if ($pdv == null) {
            return (object)[
                'status' => 'error',
                'message' => 'La caja no está asociada a ningún Punto de Ventas.',
            ];
        }

        $documentos_pdv = FacturaPos::where([
                                        ['pdv_id','=',$pdv->id],
                                        ['estado', '<>', 'Anulado']
                                    ])
                                ->whereBetween('fecha', [$fecha, $fecha])
                                ->get();

        $total_credito = $documentos_pdv->where( 'forma_pago', 'credito' )->sum('valor_total');
                
        $movimientos_tesoreria_para_pdv = $this->get_movimiento_tesoreria_pdv($documentos_pdv, $fecha, $fecha);
        
        $total_contado = 0;
        $movimiento_propinas = collect([]);
        $motivo_tesoreria_propinas = (int)config('ventas_pos.motivo_tesoreria_propinas');
        $motivo_tesoreria_datafono = (int)config('ventas_pos.motivo_tesoreria_datafono');
        foreach ($movimientos_tesoreria_para_pdv as $movimiento)
        {
            if ($movimiento->teso_motivo_id == $motivo_tesoreria_propinas) {
                $movimiento_propinas->push($movimiento);
                continue;
            }

            if ($movimiento->teso_motivo_id == $motivo_tesoreria_datafono) {
                continue;
            }

            if ($movimiento->teso_caja_id == $teso_caja_id) {
                $total_contado += $movimiento->valor_movimiento;
            }
        }
        
        $cuentas_bancarias = TesoCuentaBancaria::where('estado','Activo')->get();

        $totales_cuentas_bancarias = [];
        foreach ($cuentas_bancarias as $key => $cuenta_bancaria) {
            if ($cuenta_bancaria->id == 0) {
                continue;
            }

            $total_cuenta_bancaria = 0;
            foreach ($movimientos_tesoreria_para_pdv as $movimiento) {
                if ($movimiento->teso_cuenta_bancaria_id == $cuenta_bancaria->id) {
                    $total_cuenta_bancaria += $movimiento->valor_movimiento;
                }
            }

            $totales_cuentas_bancarias[] = [
                'label' => TesoEntidadFinanciera::find($cuenta_bancaria->entidad_financiera_id)->descripcion . " - Nro. " . $cuenta_bancaria->descripcion,
                'total' => $total_cuenta_bancaria,
                'teso_cuenta_bancaria_id' => $cuenta_bancaria->id
            ];
        }
        
        return (object)[
            'status' => 'success',
            'total_contado' => $total_contado,
            'total_credito' => $total_credito,
            'totales_cuentas_bancarias' => $totales_cuentas_bancarias
        ];
    }

    /**
     * 
     */
    public function resumen_propinas_arqueo_caja($fecha, $teso_caja_id)
    {
        $pdv = Pdv::where('caja_default_id',$teso_caja_id)->get()->first();

        if ($pdv == null) {
            return (object)[
                'status' => 'error',
                'message' => 'La caja no está asociada a ningún Punto de Ventas.',
            ];
        }

        $documentos_pdv = FacturaPos::where([
                                        ['pdv_id','=',$pdv->id],
                                        ['estado', '<>', 'Anulado']
                                    ])
                                ->whereBetween('fecha', [$fecha, $fecha])
                                ->get();
                
        $movimientos_tesoreria_propinas = $this->get_movimiento_tesoreria_propinas($documentos_pdv, $fecha, $fecha);
        
        $cuentas_bancarias = TesoCuentaBancaria::where('estado','Activo')->get();

        $totales_cuentas_bancarias = [];
        foreach ($cuentas_bancarias as $key => $cuenta_bancaria) {

            if ($cuenta_bancaria->id == 0) {
                continue;
            }

            $total_cuenta_bancaria = 0;
            foreach ($movimientos_tesoreria_propinas as $movimiento)
            {
                if ($movimiento->teso_cuenta_bancaria_id == $cuenta_bancaria->id) {
                    $total_cuenta_bancaria += $movimiento->valor_movimiento;
                }
            }

            if ( $total_cuenta_bancaria == 0) {
                continue;
            }
            
            $totales_cuentas_bancarias[] = [
                'label' => TesoEntidadFinanciera::find($cuenta_bancaria->entidad_financiera_id)->descripcion . " - Nro. " . $cuenta_bancaria->descripcion,
                'total' => $total_cuenta_bancaria
            ];
        }
        
        return (object)[
            'status' => 'success',
            'total_caja' => $movimientos_tesoreria_propinas->where('teso_caja_id', $teso_caja_id)->sum('valor_movimiento'),
            'totales_cuentas_bancarias' => $totales_cuentas_bancarias
        ];
    }

    /**
     * 
     */
    public function resumen_ingresos_bolsas($fecha, $teso_caja_id)
    {
        $pdv = Pdv::where('caja_default_id',$teso_caja_id)->get()->first();

        if ($pdv == null) {
            return (object)[
                'status' => 'error',
                'message' => 'La caja no está asociada a ningún Punto de Ventas.',
            ];
        }

        $documentos_pdv = FacturaPos::where([
                                        ['pdv_id','=',$pdv->id],
                                        ['estado', '<>', 'Anulado']
                                    ])
                                ->whereBetween('fecha', [$fecha, $fecha])
                                ->get();

        return (object)[
            'status' => 'success',
            'valor_total_bolsas' => $documentos_pdv->sum('valor_total_bolsas')
        ];
    }

    /**
     * 
     */
    public function get_ventas_credito_pdv($pdv_id, $fecha_desde, $fecha_hasta)
    {        
        $movimientos_pdv = Movimiento::get_movimiento_ventas_no_anulado( $pdv_id, $fecha_desde, $fecha_hasta);

        return $movimientos_pdv->where( 'forma_pago', 'credito');
    }

    /**
     * 
     */
    public function get_movimiento_tesoreria_pdv($documentos_pdv)
    {
        $arr_consecutivos = [];
        $arr_core_tipo_transaccion_id = [];
        $arr_core_tipo_doc_app_id = [];
        $arr_fechas = [];
        foreach ($documentos_pdv as $documento) {
            $arr_consecutivos[] = $documento->consecutivo;

            if ( !in_array($documento->core_tipo_transaccion_id, $arr_core_tipo_transaccion_id)) {
                $arr_core_tipo_transaccion_id[] = $documento->core_tipo_transaccion_id;
            }

            if ( !in_array($documento->core_tipo_doc_app_id, $arr_core_tipo_doc_app_id)) {
                $arr_core_tipo_doc_app_id[] = $documento->core_tipo_doc_app_id;
            }

            if ( !in_array($documento->fecha, $arr_fechas) ) {
                $arr_fechas[] = $documento->fecha;
            }
        }

        return TesoMovimiento::where([
                                    ['teso_motivo_id', '<>', (int)config('ventas_pos.motivo_tesoreria_propinas') ],
                                    ['teso_motivo_id', '<>', (int)config('ventas_pos.motivo_tesoreria_datafono') ]
                                ])
                                ->whereIn('core_tipo_transaccion_id', $arr_core_tipo_transaccion_id)->whereIn('core_tipo_doc_app_id', $arr_core_tipo_doc_app_id)
                                ->whereIn('consecutivo', $arr_consecutivos)
                                ->whereIn('fecha', $arr_fechas)
                                ->get();
    }

    /**
     * 
     */
    public function get_movimiento_tesoreria_propinas($documentos_pdv)
    {
        $arr_consecutivos = [];
        $arr_core_tipo_transaccion_id = [];
        $arr_core_tipo_doc_app_id = [];
        $arr_fechas = [];
        foreach ($documentos_pdv as $documento) {
            $arr_consecutivos[] = $documento->consecutivo;

            if ( !in_array($documento->core_tipo_transaccion_id, $arr_core_tipo_transaccion_id)) {
                $arr_core_tipo_transaccion_id[] = $documento->core_tipo_transaccion_id;
            }

            if ( !in_array($documento->core_tipo_doc_app_id, $arr_core_tipo_doc_app_id)) {
                $arr_core_tipo_doc_app_id[] = $documento->core_tipo_doc_app_id;
            }

            if ( !in_array($documento->fecha, $arr_fechas) ) {
                $arr_fechas[] = $documento->fecha;
            }
        }

        return TesoMovimiento::where([
                                    ['teso_motivo_id', '=', (int)config('ventas_pos.motivo_tesoreria_propinas') ]
                                ])
                                ->whereIn('core_tipo_transaccion_id', $arr_core_tipo_transaccion_id)->whereIn('core_tipo_doc_app_id', $arr_core_tipo_doc_app_id)
                                ->whereIn('consecutivo', $arr_consecutivos)
                                ->whereIn('fecha', $arr_fechas)
                                ->get();
    }

    /**
     * 
     */
    public function get_ventas_por_medios_pago_con_iva($pdv_id, $fecha_desde, $fecha_hasta)
    {
        $documentos_pdv = FacturaPos::where([
                                            ['pdv_id','=',$pdv_id],
                                            ['estado', '<>', 'Anulado']
                                        ])
                                    ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                                    ->get();

        $movimiento_tesoreria_pdv = $this->get_movimiento_tesoreria_pdv($documentos_pdv, $fecha_desde, $fecha_hasta);
    
        $movin_por_medio_recaudo = $movimiento_tesoreria_pdv->groupBy('teso_medio_recaudo_id');

        $ventas_por_medios_pago_con_iva  = collect([]);

        $total_venta_contado_con_iva = $movimiento_tesoreria_pdv->sum('valor_movimiento');
        foreach ($movin_por_medio_recaudo as $movin_grupo) {
            
            $primera_linea_movin_grupo = $movin_grupo->first();

            $porcentaje_participacion_total_ventas = 0;
            if ($total_venta_contado_con_iva != 0) {
                $porcentaje_participacion_total_ventas = $movin_grupo->sum('valor_movimiento') / $total_venta_contado_con_iva;
            }
            
            $ventas_por_medios_pago_con_iva->push((object)[
                    'medio_pago' => $primera_linea_movin_grupo->medio_pago->descripcion,
                    'total_venta' => $movin_grupo->sum('valor_movimiento'),
                    'porcentaje_participacion_total_ventas' => $porcentaje_participacion_total_ventas
                ]);
        }

        return $ventas_por_medios_pago_con_iva;
    }

    /**
     * 
     */
    public function get_ventas_por_caja_bancos($pdv_id, $fecha_desde, $fecha_hasta)
    {
        $documentos_pdv = FacturaPos::where([
                                            ['pdv_id','=',$pdv_id],
                                            ['estado', '<>', 'Anulado']
                                        ])
                                    ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                                    ->get();

        $movimiento_tesoreria_pdv = $this->get_movimiento_tesoreria_pdv($documentos_pdv, $fecha_desde, $fecha_hasta);
        
        $ventas_por_medios_pago_con_iva  = collect([]);

        $total_venta_contado_con_iva = $movimiento_tesoreria_pdv->sum('valor_movimiento');
        
        $movin_cajas = $movimiento_tesoreria_pdv->groupBy('teso_caja_id');
        foreach ($movin_cajas as $caja_id => $movin_grupo) {

            if ( $caja_id == 0) {
                continue; // No se debe incluir el movimiento de caja 0
            }
            
            $primera_linea_movin_grupo = $movin_grupo->first();

            $porcentaje_participacion_total_ventas = 0;
            if ($total_venta_contado_con_iva != 0) {
                $porcentaje_participacion_total_ventas = $movin_grupo->sum('valor_movimiento') / $total_venta_contado_con_iva;
            }
            
            $ventas_por_medios_pago_con_iva->push((object)[
                    'caja_banco' => $primera_linea_movin_grupo->caja->descripcion,
                    'total_venta' => $movin_grupo->sum('valor_movimiento'),
                    'porcentaje_participacion_total_ventas' => $porcentaje_participacion_total_ventas
                ]);
        }

        /**
         * 
         */
        $movin_bancos = $movimiento_tesoreria_pdv->groupBy('teso_cuenta_bancaria_id');
        foreach ($movin_bancos as $banco_id => $movin_grupo) {

            if ( $banco_id == 0) {
                continue; // No se debe incluir el movimiento de caja 0
            }
            
            $primera_linea_movin_grupo = $movin_grupo->first();

            $porcentaje_participacion_total_ventas = 0;
            if ($total_venta_contado_con_iva != 0) {
                $porcentaje_participacion_total_ventas = $movin_grupo->sum('valor_movimiento') / $total_venta_contado_con_iva;
            }
            
            $ventas_por_medios_pago_con_iva->push((object)[
                    'caja_banco' => $primera_linea_movin_grupo->cuenta_bancaria->descripcion,
                    'total_venta' => $movin_grupo->sum('valor_movimiento'),
                    'porcentaje_participacion_total_ventas' => $porcentaje_participacion_total_ventas
                ]);
        }

        return $ventas_por_medios_pago_con_iva;
    }

    /**
     * 
     */
    public function get_movimentos_trasacciones_recaudos($fecha)
    {
        $arr_core_tipo_transaccion_id = [
            8, // 8 = Recaudos Generales
            (int)config('tesoreria.recaudos_cxc_tipo_transaccion_id')
        ];

        return TesoMovimiento::where([
                                    ['teso_motivo_id', '<>', (int)config('ventas_pos.motivo_tesoreria_propinas') ],
                                    ['teso_motivo_id', '<>', (int)config('ventas_pos.motivo_tesoreria_datafono') ],
                                    ['fecha', '=', $fecha]
                                ])
                                ->whereIn('core_tipo_transaccion_id', $arr_core_tipo_transaccion_id)
                                ->get();
    }
    public function get_movimentos_cuentas_bancarias($fecha)
    {
        return TesoMovimiento::where([
                                    ['teso_motivo_id', '<>', (int)config('ventas_pos.motivo_tesoreria_propinas') ],
                                    ['teso_motivo_id', '<>', (int)config('ventas_pos.motivo_tesoreria_datafono') ],
                                    ['fecha', '=', $fecha]
                                ])
                                ->where('teso_caja_id', 0)
                                ->groupBy('teso_cuenta_bancaria_id')
                                ->get();
    }
}