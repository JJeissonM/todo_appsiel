<?php

use App\CxP\CxpAbono;
use App\CxP\CxpMovimiento;
use App\Contabilidad\ContabMovimiento;
use App\Http\Controllers\Tesoreria\PagoCxpController;
use App\Tesoreria\TesoDocEncabezado;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoCxpDocumentoVistaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_permite_pago_directo_sin_documentos_de_cartera()
    {
        $controller = new PagoCxpController();
        $encabezado = new TesoDocEncabezado();

        foreach (['', '[]', '[{"id_doc":"","abono":""}]'] as $lineasRegistros) {
            $request = new Request(['lineas_registros' => $lineasRegistros]);

            $this->assertSame(0.0, (float)$controller->almacenar_registros_cxp($request, $encabezado));
        }
    }

    public function test_genera_detalle_del_encabezado_desde_los_documentos_seleccionados()
    {
        $documentos = CxpMovimiento::orderBy('id')->take(2)->get();
        $this->assertCount(2, $documentos);

        $documentos[0]->detalle = 'Detalle del primer documento';
        $documentos[0]->save();
        $documentos[1]->detalle = 'Detalle del segundo documento';
        $documentos[1]->save();

        $configuracionOriginal = config('tesoreria.generar_detalle_pago_cxp_desde_documentos');

        try {
            $request = new Request([
                'descripcion' => '   ',
                'lineas_registros' => json_encode([
                    ['id_doc' => $documentos[1]->id, 'abono' => 10],
                    ['id_doc' => $documentos[0]->id, 'abono' => 20],
                    ['id_doc' => '', 'abono' => '']
                ])
            ]);

            config(['tesoreria.generar_detalle_pago_cxp_desde_documentos' => 0]);
            $this->invocarGeneracionDetalle($request);
            $this->assertSame('   ', $request->descripcion);

            config(['tesoreria.generar_detalle_pago_cxp_desde_documentos' => 1]);
            $this->invocarGeneracionDetalle($request);

            $this->assertSame(
                'Detalle del segundo documento | Detalle del primer documento',
                $request->descripcion
            );

            $requestConDetalle = new Request([
                'descripcion' => 'Detalle escrito por el usuario',
                'lineas_registros' => $request->lineas_registros
            ]);
            $this->invocarGeneracionDetalle($requestConDetalle);
            $this->assertSame('Detalle escrito por el usuario', $requestConDetalle->descripcion);
        } finally {
            config([
                'tesoreria.generar_detalle_pago_cxp_desde_documentos' => is_null($configuracionOriginal)
                    ? 0
                    : $configuracionOriginal
            ]);
        }
    }

    public function test_configuracion_para_generar_el_detalle_esta_disponible_y_desactivada_por_defecto()
    {
        $vista = file_get_contents(resource_path('views/core/config_aplicacion/tesoreria.blade.php'));

        $this->assertSame('0', (string)config('tesoreria.generar_detalle_pago_cxp_desde_documentos', 0));
        $this->assertContains("Form::bsSelect('generar_detalle_pago_cxp_desde_documentos'", $vista);
    }

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

    public function test_anulacion_reversa_una_vez_cada_abono_de_documentos_con_la_misma_identidad()
    {
        $usuario = User::where('empresa_id', 1)->first();
        $movimientoBase = CxpMovimiento::where('core_empresa_id', 1)->first();

        $this->assertNotNull($usuario);
        $this->assertNotNull($movimientoBase);
        $this->be($usuario);

        $terceroEncabezado = DB::table('core_terceros')
            ->where('id', '<>', $movimientoBase->core_tercero_id)
            ->value('id');
        $this->assertNotNull($terceroEncabezado);

        $consecutivoPago = (int)TesoDocEncabezado::where('core_tipo_transaccion_id', 33)
            ->max('consecutivo') + 1000;
        $consecutivoCxp = (int)CxpMovimiento::max('consecutivo') + 1000;

        $pago = TesoDocEncabezado::create([
            'core_tipo_transaccion_id' => 33,
            'core_tipo_doc_app_id' => 23,
            'consecutivo' => $consecutivoPago,
            'fecha' => date('Y-m-d'),
            'core_empresa_id' => 1,
            'core_tercero_id' => $terceroEncabezado,
            'codigo_referencia_tercero' => '',
            'teso_tipo_motivo' => '',
            'documento_soporte' => '',
            'descripcion' => 'Pago para probar anulación',
            'teso_medio_recaudo_id' => 1,
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'valor_total' => 300,
            'estado' => 'Activo',
            'creado_por' => $usuario->email,
            'modificado_por' => ''
        ]);

        $movimientos = collect([100, 200])->map(function ($valor) use ($movimientoBase, $consecutivoCxp, $usuario) {
            return CxpMovimiento::create([
                'core_tipo_transaccion_id' => $movimientoBase->core_tipo_transaccion_id,
                'core_tipo_doc_app_id' => $movimientoBase->core_tipo_doc_app_id,
                'consecutivo' => $consecutivoCxp,
                'core_empresa_id' => 1,
                'core_tercero_id' => $movimientoBase->core_tercero_id,
                'modelo_referencia_tercero_index' => $movimientoBase->modelo_referencia_tercero_index,
                'referencia_tercero_id' => $movimientoBase->referencia_tercero_id,
                'doc_proveedor_prefijo' => 'TEST',
                'doc_proveedor_consecutivo' => (string)$consecutivoCxp,
                'fecha' => date('Y-m-d'),
                'fecha_vencimiento' => date('Y-m-d'),
                'valor_documento' => $valor,
                'valor_pagado' => $valor,
                'saldo_pendiente' => 0,
                'estado' => 'Pagado',
                'detalle' => 'Línea CxP para probar anulación',
                'creado_por' => $usuario->email,
                'modificado_por' => ''
            ]);
        });

        foreach ($movimientos as $movimiento) {
            CxpAbono::create([
                'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
                'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
                'consecutivo' => $pago->consecutivo,
                'core_empresa_id' => $movimiento->core_empresa_id,
                // Simula los abonos históricos que guardaban el tercero del
                // encabezado aunque la línea de CxP perteneciera a otro.
                'core_tercero_id' => $terceroEncabezado,
                'modelo_referencia_tercero_index' => $movimiento->modelo_referencia_tercero_index,
                'referencia_tercero_id' => $movimiento->referencia_tercero_id,
                'fecha' => $pago->fecha,
                'doc_cxp_transacc_id' => $movimiento->core_tipo_transaccion_id,
                'doc_cxp_tipo_doc_id' => $movimiento->core_tipo_doc_app_id,
                'doc_cxp_consecutivo' => $movimiento->consecutivo,
                'doc_cruce_transacc_id' => 0,
                'doc_cruce_tipo_doc_id' => 0,
                'doc_cruce_consecutivo' => 0,
                'abono' => $movimiento->valor_documento,
                'creado_por' => $usuario->email,
                'modificado_por' => ''
            ]);
        }

        $response = (new PagoCxpController())->anular_pago_cxp($pago->id);

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertSame('Anulado', TesoDocEncabezado::find($pago->id)->estado);
        $this->assertSame(0, CxpAbono::where('core_tipo_transaccion_id', 33)
            ->where('core_tipo_doc_app_id', 23)
            ->where('consecutivo', $consecutivoPago)
            ->count());

        foreach ($movimientos as $movimiento) {
            $movimiento = CxpMovimiento::find($movimiento->id);
            $this->assertSame('Pendiente', $movimiento->estado);
            $this->assertEquals(0, $movimiento->valor_pagado);
            $this->assertEquals($movimiento->valor_documento, $movimiento->saldo_pendiente);
        }
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

    protected function invocarGeneracionDetalle(Request $request)
    {
        $metodo = new ReflectionMethod(PagoCxpController::class, 'completar_detalle_encabezado_desde_documentos');
        $metodo->setAccessible(true);
        $metodo->invoke(new PagoCxpController(), $request);
    }
}
