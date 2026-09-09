<?php

use App\VentasPos\FacturaPos;
use App\VentasPos\Services\ElectronicInvoicePrintService;
use App\FacturacionElectronica\Factura;
use Illuminate\Support\Facades\DB;

class ElectronicInvoicePrintServiceTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        DB::beginTransaction();
        config(['facturacion_electronica.transaction_type_id_default' => 52]);
    }

    protected function tearDown()
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_resuelve_factura_electronica_relacionada_desde_encabezado_pos()
    {
        $posId = mt_rand(900000000, 999999999);
        $companyId = mt_rand(800000000, 899999999);
        $documentTypeId = (int)DB::table('core_tipos_docs_apps')->value('id');
        $electronicId = $this->insertElectronicInvoice([
            'core_empresa_id' => $companyId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'ventas_doc_relacionado_id' => $posId,
            'consecutivo' => mt_rand(800000000, 899999999)
        ]);

        $posInvoice = $this->makePosInvoice(
            $posId,
            $companyId,
            $documentTypeId,
            mt_rand(700000000, 799999999)
        );

        $resolved = (new ElectronicInvoicePrintService())->resolveElectronicInvoice($posInvoice);

        $this->assertNotNull($resolved);
        $this->assertSame($electronicId, (int)$resolved->id);
    }

    public function test_usa_equivalencia_para_conversiones_antiguas_sin_relacion_pos()
    {
        $posId = mt_rand(900000000, 999999999);
        $companyId = mt_rand(800000000, 899999999);
        $documentTypeId = (int)DB::table('core_tipos_docs_apps')->value('id');
        $consecutivo = mt_rand(800000000, 899999999);
        $electronicId = $this->insertElectronicInvoice([
            'core_empresa_id' => $companyId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'ventas_doc_relacionado_id' => 0,
            'consecutivo' => $consecutivo
        ]);

        $posInvoice = $this->makePosInvoice(
            $posId,
            $companyId,
            $documentTypeId,
            $consecutivo
        );

        $resolved = (new ElectronicInvoicePrintService())->resolveElectronicInvoice($posInvoice);

        $this->assertNotNull($resolved);
        $this->assertSame($electronicId, (int)$resolved->id);
    }

    public function test_conserva_el_encabezado_electronico_en_la_ruta_de_impresion_de_ventas()
    {
        $companyId = mt_rand(800000000, 899999999);
        $documentTypeId = (int)DB::table('core_tipos_docs_apps')->value('id');
        $electronicId = $this->insertElectronicInvoice([
            'core_empresa_id' => $companyId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'ventas_doc_relacionado_id' => 0,
            'consecutivo' => mt_rand(800000000, 899999999)
        ]);

        $resolved = (new ElectronicInvoicePrintService())
            ->resolveElectronicInvoice(Factura::find($electronicId));

        $this->assertNotNull($resolved);
        $this->assertSame($electronicId, (int)$resolved->id);
    }

    protected function makePosInvoice($id, $companyId, $documentTypeId, $consecutivo)
    {
        $invoice = new FacturaPos();
        $invoice->id = (int)$id;
        $invoice->core_empresa_id = (int)$companyId;
        $invoice->core_tipo_transaccion_id = 52;
        $invoice->core_tipo_doc_app_id = (int)$documentTypeId;
        $invoice->consecutivo = $consecutivo;

        return $invoice;
    }

    protected function insertElectronicInvoice(array $overrides)
    {
        $now = date('Y-m-d H:i:s');
        $data = [
            'core_tipo_transaccion_id' => 52,
            'core_tipo_doc_app_id' => $overrides['core_tipo_doc_app_id'],
            'consecutivo' => $overrides['consecutivo'],
            'fecha' => date('Y-m-d'),
            'core_empresa_id' => $overrides['core_empresa_id'],
            'core_tercero_id' => 0,
            'remision_doc_encabezado_id' => '0',
            'ventas_doc_relacionado_id' => $overrides['ventas_doc_relacionado_id'],
            'cliente_id' => 0,
            'contacto_cliente_id' => 0,
            'vendedor_id' => 0,
            'forma_pago' => 'contado',
            'fecha_entrega' => date('Y-m-d'),
            'hora_entrega' => date('H:i:s'),
            'fecha_vencimiento' => date('Y-m-d'),
            'orden_compras' => '',
            'descripcion' => 'Prueba impresion factura electronica POS',
            'valor_total' => 0,
            'efectivo_recibido' => 0,
            'total_efectivo_recibido' => 0,
            'valor_ajuste_al_peso' => 0,
            'valor_total_bolsas' => 0,
            'valor_total_cambio' => 0,
            'estado' => 'Enviada',
            'creado_por' => 'pruebas@appsiel.com.co',
            'modificado_por' => '',
            'created_at' => $now,
            'updated_at' => $now,
            'plazo_entrega_id' => 0
        ];

        return DB::table('vtas_doc_encabezados')->insertGetId($data);
    }
}
