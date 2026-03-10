<?php 

namespace App\VentasPos\Services;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Impuesto;
use App\CxC\CxcMovimiento;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\VtasMovimiento;
use App\VentasPos\DocRegistro;
use App\VentasPos\FacturaPos;
use App\VentasPos\Movimiento;
use Illuminate\Support\Facades\DB;

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

        $documento->valor_total = $total_documento;
        $documento->save();

        $obj_sales_serv->contabilizar_movimiento_debito_para_recontabilizacion( $documento );

        return true;
    }

    public function reconstruir_movimientos_y_recontabilizar_factura($documento_id)
    {
        try {
            return DB::transaction(function () use ($documento_id) {
                $documento = FacturaPos::with(['lineas_registros', 'cliente.vendedor', 'pdv'])->find($documento_id);
                if (is_null($documento)) {
                    return (object)[
                        'status' => 'error',
                        'message' => 'Documento no encontrado.'
                    ];
                }

                if ($documento->estado == 'Pendiente') {
                    return (object)[
                        'status' => 'error',
                        'message' => 'Factura en estado Pendiente. Aún no tiene movimiento de ventas.'
                    ];
                }

                $summary = [
                    'documento' => $documento->get_label_documento(),
                    'lineas_procesadas' => 0,
                    'lineas_actualizadas' => 0,
                    'total_anterior' => (float)$documento->valor_total,
                    'total_nuevo' => 0,
                    'mov_pos_eliminados' => 0,
                    'mov_pos_creados' => 0,
                    'mov_vtas_eliminados' => 0,
                    'mov_vtas_creados' => 0,
                    'contab_eliminados' => 0,
                    'contab_creados' => 0,
                    'teso_lineas_afectadas' => 0,
                    'teso_valor_anterior' => 0,
                    'teso_valor_nuevo' => 0,
                    'cxc_afectado' => 0,
                    'cxc_valor_anterior' => 0,
                    'cxc_valor_nuevo' => 0,
                    'observaciones' => []
                ];

                foreach ($documento->lineas_registros as $linea) {
                    $summary['lineas_procesadas']++;

                    if ($this->recalcular_linea_documento($linea)) {
                        $summary['lineas_actualizadas']++;
                    }
                }

                $summary['total_nuevo'] = (float)DocRegistro::where('vtas_pos_doc_encabezado_id', $documento->id)->sum('precio_total');
                $documento->valor_total = $summary['total_nuevo'];
                $documento->save();

                $array_wheres = [
                    ['core_tipo_transaccion_id', '=', $documento->core_tipo_transaccion_id],
                    ['core_tipo_doc_app_id', '=', $documento->core_tipo_doc_app_id],
                    ['consecutivo', '=', $documento->consecutivo]
                ];

                $summary['mov_pos_eliminados'] = Movimiento::where($array_wheres)->count();
                Movimiento::where($array_wheres)->delete();

                $summary['mov_vtas_eliminados'] = VtasMovimiento::where($array_wheres)->count();
                VtasMovimiento::where($array_wheres)->delete();

                $documento->load(['lineas_registros', 'cliente.vendedor', 'pdv']);
                $datos = $documento->toArray();
                $datos['zona_id'] = $documento->cliente->zona_id;
                $datos['clase_cliente_id'] = $documento->cliente->clase_cliente_id;
                $datos['equipo_ventas_id'] = $documento->cliente->vendedor ? $documento->cliente->vendedor->equipo_ventas_id : 0;
                $datos['inv_bodega_id'] = $documento->pdv ? $documento->pdv->bodega_default_id : 0;

                foreach ($documento->lineas_registros as $linea) {
                    VtasMovimiento::create($datos + $linea->toArray());
                    $summary['mov_vtas_creados']++;

                    Movimiento::create($datos + $linea->toArray());
                    $summary['mov_pos_creados']++;
                }

                $summary['contab_eliminados'] = ContabMovimiento::where($array_wheres)->count();

                if ($documento->forma_pago == 'contado') {
                    $teso_before = TesoMovimiento::where($array_wheres)->get();
                    $summary['teso_lineas_afectadas'] = $teso_before->count();
                    $summary['teso_valor_anterior'] = (float)$teso_before->sum('valor_movimiento');
                }

                if ($documento->forma_pago == 'credito') {
                    $cxc_before = CxcMovimiento::where($array_wheres)->first();
                    if (!is_null($cxc_before)) {
                        $summary['cxc_afectado'] = 1;
                        $summary['cxc_valor_anterior'] = (float)$cxc_before->valor_documento;
                    }
                }

                $this->recontabilizar_factura($documento->id);

                $summary['contab_creados'] = ContabMovimiento::where($array_wheres)->count();

                if ($documento->forma_pago == 'contado') {
                    $summary['teso_valor_nuevo'] = (float)TesoMovimiento::where($array_wheres)->sum('valor_movimiento');
                    if ($summary['teso_lineas_afectadas'] > 0 && abs($summary['teso_valor_anterior'] - $summary['teso_valor_nuevo']) > 0.01) {
                        $summary['observaciones'][] = 'Se ajustaron valores en tesorería para mantenerlos alineados con el nuevo total.';
                    }
                }

                if ($documento->forma_pago == 'credito') {
                    $cxc_after = CxcMovimiento::where($array_wheres)->first();
                    if (!is_null($cxc_after)) {
                        $summary['cxc_valor_nuevo'] = (float)$cxc_after->valor_documento;
                        if ((float)$cxc_after->saldo_pendiente < 0) {
                            $summary['observaciones'][] = 'El saldo pendiente de CxC quedó negativo después del ajuste.';
                        }
                    }
                }

                return (object)[
                    'status' => 'success',
                    'summary' => $summary
                ];
            });
        } catch (\Throwable $th) {
            return (object)[
                'status' => 'error',
                'message' => $th->getMessage()
            ];
        }
    }

    protected function recalcular_linea_documento(DocRegistro $linea)
    {
        $cantidad = (float)$linea->cantidad;
        $precio_unitario = (float)$linea->precio_unitario;
        $tasa_descuento = (float)$linea->tasa_descuento;

        $tasa_impuesto = $this->get_tasa_impuesto_por_id($linea->impuesto_id, (float)$linea->tasa_impuesto);

        $precio_bruto_total = round($precio_unitario * $cantidad, 2);
        $valor_total_descuento = (float)$linea->valor_total_descuento;
        if ($tasa_descuento > 0) {
            $valor_total_descuento = round($precio_bruto_total * $tasa_descuento / 100, 2);
        }

        $precio_total = round($precio_bruto_total - $valor_total_descuento, 2);
        if ($precio_total < 0) {
            $precio_total = 0;
        }

        $divisor = 1 + ($tasa_impuesto / 100);
        $base_impuesto_total = $divisor != 0 ? round($precio_total / $divisor, 2) : $precio_total;
        $valor_total_impuesto = round($precio_total - $base_impuesto_total, 2);

        $base_impuesto = 0;
        $valor_impuesto = 0;
        if ($cantidad != 0) {
            $base_impuesto = round($base_impuesto_total / $cantidad, 6);
            $valor_impuesto = round($valor_total_impuesto / $cantidad, 6);
        }

        $linea_anterior = [
            'tasa_impuesto' => (float)$linea->tasa_impuesto,
            'base_impuesto' => (float)$linea->base_impuesto,
            'valor_impuesto' => (float)$linea->valor_impuesto,
            'base_impuesto_total' => (float)$linea->base_impuesto_total,
            'precio_total' => (float)$linea->precio_total,
            'valor_total_descuento' => (float)$linea->valor_total_descuento
        ];

        $linea->tasa_impuesto = $tasa_impuesto;
        $linea->base_impuesto = $base_impuesto;
        $linea->valor_impuesto = $valor_impuesto;
        $linea->base_impuesto_total = $base_impuesto_total;
        $linea->precio_total = $precio_total;
        $linea->valor_total_descuento = $valor_total_descuento;
        $linea->save();

        $linea_nueva = [
            'tasa_impuesto' => (float)$linea->tasa_impuesto,
            'base_impuesto' => (float)$linea->base_impuesto,
            'valor_impuesto' => (float)$linea->valor_impuesto,
            'base_impuesto_total' => (float)$linea->base_impuesto_total,
            'precio_total' => (float)$linea->precio_total,
            'valor_total_descuento' => (float)$linea->valor_total_descuento
        ];

        return $linea_anterior != $linea_nueva;
    }

    protected function get_tasa_impuesto_por_id($impuesto_id, $tasa_fallback = 0)
    {
        $impuesto_id = (int)$impuesto_id;
        if ($impuesto_id <= 0) {
            return (float)$tasa_fallback;
        }

        $impuesto = Impuesto::find($impuesto_id);
        if (is_null($impuesto)) {
            return (float)$tasa_fallback;
        }

        return (float)$impuesto->tasa_impuesto;
    }
        
}
