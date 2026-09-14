<?php

use App\CxP\CxpAbono;
use App\CxP\CxpMovimiento;
use App\Contabilidad\ContabMovimiento;
use App\Http\Controllers\Tesoreria\PagoCxpController;
use App\Tesoreria\TesoDocEncabezado;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PagoCxpDocumentoVistaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_documento_pagado_usa_datos_del_movimiento_cxp_sin_depender_del_encabezado_origen()
    {
        $pago = TesoDocEncabezado::first();
        $movimiento = CxpMovimiento::first();

        $this->assertNotNull($pago);
        $this->assertNotNull($movimiento);

        $abono = CxpAbono::create([
            'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
            'consecutivo' => $pago->consecutivo,
            'core_empresa_id' => $movimiento->core_empresa_id,
            'core_tercero_id' => $movimiento->core_tercero_id,
            'modelo_referencia_tercero_index' => $movimiento->modelo_referencia_tercero_index,
            'referencia_tercero_id' => $movimiento->referencia_tercero_id,
            'fecha' => $pago->fecha,
            'doc_cxp_transacc_id' => $movimiento->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id' => $movimiento->core_tipo_doc_app_id,
            'doc_cxp_consecutivo' => $movimiento->consecutivo,
            'abono' => 100,
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => ''
        ]);

        $linea = CxpAbono::get_documentos_abonados($pago)->where('id', $abono->id)->first();

        $this->assertNotNull($linea);
        $this->assertSame((string)$movimiento->fecha, (string)$linea->documento_fecha);
        $this->assertNotEmpty($linea->documento_descripcion);
    }

    public function test_items_apm_asocian_la_factura_solo_al_debito_y_muestran_creditos_negativos()
    {
        $base = ContabMovimiento::where('contab_cuenta_id', '>', 0)
            ->where('core_tercero_id', '>', 0)
            ->first();
        $movimientoCxp = CxpMovimiento::first();
        $this->assertNotNull($base);
        $this->assertNotNull($movimientoCxp);

        $consecutivo = (int)ContabMovimiento::max('consecutivo') + 1000;
        $encabezado = new TesoDocEncabezado([
            'core_tipo_transaccion_id' => 33,
            'core_tipo_doc_app_id' => $base->core_tipo_doc_app_id,
            'consecutivo' => $consecutivo
        ]);

        CxpAbono::create([
            'core_tipo_transaccion_id' => $encabezado->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $encabezado->core_tipo_doc_app_id,
            'consecutivo' => $encabezado->consecutivo,
            'core_empresa_id' => $movimientoCxp->core_empresa_id,
            'core_tercero_id' => $movimientoCxp->core_tercero_id,
            'modelo_referencia_tercero_index' => $movimientoCxp->modelo_referencia_tercero_index,
            'referencia_tercero_id' => $movimientoCxp->referencia_tercero_id,
            'fecha' => date('Y-m-d'),
            'doc_cxp_transacc_id' => $movimientoCxp->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id' => $movimientoCxp->core_tipo_doc_app_id,
            'doc_cxp_consecutivo' => $movimientoCxp->consecutivo,
            'abono' => 125.50,
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => ''
        ]);

        foreach ([[125.50, 0], [0, -125.50]] as $valores) {
            ContabMovimiento::create([
                'core_tipo_transaccion_id' => $encabezado->core_tipo_transaccion_id,
                'core_tipo_doc_app_id' => $encabezado->core_tipo_doc_app_id,
                'consecutivo' => $encabezado->consecutivo,
                'fecha' => date('Y-m-d'),
                'core_empresa_id' => $base->core_empresa_id,
                'core_tercero_id' => $base->core_tercero_id,
                'contab_cuenta_id' => $base->contab_cuenta_id,
                'valor_debito' => $valores[0],
                'valor_credito' => $valores[1],
                'valor_saldo' => $valores[0] + $valores[1],
                'detalle_operacion' => 'Prueba APM',
                'estado' => 'Activo',
                'creado_por' => 'test@appsiel.com'
            ]);
        }

        $metodo = new ReflectionMethod(PagoCxpController::class, 'build_apm_accounting_summary');
        $metodo->setAccessible(true);
        $summary = $metodo->invoke(new PagoCxpController(), $encabezado);
        $items = $summary['items'];
        $documentoPagado = CxpAbono::get_documentos_abonados($encabezado)->first();
        $itemDebito = collect($items)->filter(function ($item) {
            return $item['Debit'] === '$126';
        })->first();
        $itemCredito = collect($items)->filter(function ($item) {
            return $item['Credit'] === '$126';
        })->first();

        $this->assertCount(2, $items);
        $this->assertSame(126, $summary['total_debit']);
        $this->assertSame($summary['total_debit'], $summary['total_credit']);
        $this->assertNotNull($documentoPagado);
        $this->assertSame((string)$documentoPagado->documento_prefijo_consecutivo, $itemDebito['Reference']);
        $this->assertSame('-', $itemCredito['Reference']);
    }

    public function test_resumen_apm_rechaza_totales_descuadrados_despues_del_redondeo()
    {
        $base = ContabMovimiento::where('contab_cuenta_id', '>', 0)
            ->where('core_tercero_id', '>', 0)
            ->first();
        $this->assertNotNull($base);

        $consecutivo = (int)ContabMovimiento::max('consecutivo') + 2000;
        $encabezado = new TesoDocEncabezado([
            'core_tipo_transaccion_id' => 33,
            'core_tipo_doc_app_id' => $base->core_tipo_doc_app_id,
            'consecutivo' => $consecutivo
        ]);

        foreach ([[0.50, 0], [0.50, 0], [0, -1.00]] as $valores) {
            ContabMovimiento::create([
                'core_tipo_transaccion_id' => $encabezado->core_tipo_transaccion_id,
                'core_tipo_doc_app_id' => $encabezado->core_tipo_doc_app_id,
                'consecutivo' => $encabezado->consecutivo,
                'fecha' => date('Y-m-d'),
                'core_empresa_id' => $base->core_empresa_id,
                'core_tercero_id' => $base->core_tercero_id,
                'contab_cuenta_id' => $base->contab_cuenta_id,
                'valor_debito' => $valores[0],
                'valor_credito' => $valores[1],
                'valor_saldo' => $valores[0] + $valores[1],
                'detalle_operacion' => 'Prueba redondeo APM',
                'estado' => 'Activo',
                'creado_por' => 'test@appsiel.com'
            ]);
        }

        $this->setExpectedException('RuntimeException', 'los valores redondeados están descuadrados');
        $metodo = new ReflectionMethod(PagoCxpController::class, 'build_apm_accounting_summary');
        $metodo->setAccessible(true);
        $metodo->invoke(new PagoCxpController(), $encabezado);
    }
}
