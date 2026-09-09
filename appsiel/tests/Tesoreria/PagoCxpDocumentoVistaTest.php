<?php

use App\CxP\CxpAbono;
use App\CxP\CxpMovimiento;
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
}
