<?php

use App\Ventas\Services\DocumentHeaderService;
use App\Ventas\VtasDocEncabezado;
use Illuminate\Support\Facades\DB;

class TestableSalesDocumentHeaderService extends DocumentHeaderService
{
    public function cancelRelatedPosDocument(VtasDocEncabezado $documentHeader, $modifiedBy)
    {
        $this->cancel_related_pos_document($documentHeader, $modifiedBy);
    }
}

class DocumentHeaderServicePosCancellationTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_anula_el_encabezado_pos_equivalente_de_una_factura_electronica()
    {
        $consecutivo = mt_rand(800000000, 899999999);
        $documentTypeId = (int)DB::table('core_tipos_docs_apps')->value('id');
        $this->insertPosHeader(52, $documentTypeId, $consecutivo, 7, 'Contabilizado');
        $otherCompanyId = $this->insertPosHeader(52, $documentTypeId, $consecutivo, 8, 'Contabilizado');

        $documentHeader = new VtasDocEncabezado([
            'core_tipo_transaccion_id' => 52,
            'core_tipo_doc_app_id' => $documentTypeId,
            'consecutivo' => $consecutivo,
            'core_empresa_id' => 7,
        ]);

        (new TestableSalesDocumentHeaderService())
            ->cancelRelatedPosDocument($documentHeader, 'pruebas@appsiel.com.co');

        $this->assertSame('Anulado', DB::table('vtas_pos_doc_encabezados')
            ->where('core_empresa_id', 7)
            ->where('core_tipo_transaccion_id', 52)
            ->where('core_tipo_doc_app_id', $documentTypeId)
            ->where('consecutivo', $consecutivo)
            ->value('estado'));
        $this->assertSame('Contabilizado', DB::table('vtas_pos_doc_encabezados')->where('id', $otherCompanyId)->value('estado'));
    }

    public function test_no_modifica_pos_para_una_transaccion_que_no_es_factura_electronica()
    {
        $consecutivo = mt_rand(700000000, 799999999);
        $documentTypeId = (int)DB::table('core_tipos_docs_apps')->value('id');
        $posHeaderId = $this->insertPosHeader(23, $documentTypeId, $consecutivo, 7, 'Contabilizado');

        $documentHeader = new VtasDocEncabezado([
            'core_tipo_transaccion_id' => 23,
            'core_tipo_doc_app_id' => $documentTypeId,
            'consecutivo' => $consecutivo,
            'core_empresa_id' => 7,
        ]);

        (new TestableSalesDocumentHeaderService())
            ->cancelRelatedPosDocument($documentHeader, 'pruebas@appsiel.com.co');

        $this->assertSame('Contabilizado', DB::table('vtas_pos_doc_encabezados')->where('id', $posHeaderId)->value('estado'));
    }

    protected function insertPosHeader($transactionId, $documentTypeId, $consecutivo, $companyId, $estado)
    {
        $now = date('Y-m-d H:i:s');
        $thirdPartyId = (int)DB::table('core_terceros')->value('id');
        $posId = (int)DB::table('vtas_pos_puntos_de_ventas')->value('id');

        return DB::table('vtas_pos_doc_encabezados')->insertGetId([
            'core_tipo_transaccion_id' => $transactionId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'consecutivo' => $consecutivo,
            'fecha' => date('Y-m-d'),
            'core_empresa_id' => $companyId,
            'core_tercero_id' => $thirdPartyId,
            'remision_doc_encabezado_id' => 0,
            'ventas_doc_relacionado_id' => 0,
            'cliente_id' => 0,
            'vendedor_id' => 0,
            'pdv_id' => $posId,
            'cajero_id' => 0,
            'forma_pago' => 'contado',
            'fecha_entrega' => $now,
            'fecha_vencimiento' => date('Y-m-d'),
            'lineas_registros_medios_recaudos' => '[]',
            'descripcion' => 'Prueba sincronizacion anulacion FE-POS',
            'valor_total' => 0,
            'efectivo_recibido' => 0,
            'estado' => $estado,
            'creado_por' => 'pruebas@appsiel.com.co',
            'modificado_por' => '',
            'created_at' => $now,
            'updated_at' => $now,
            'lote_acumulacion' => '',
        ]);
    }
}
