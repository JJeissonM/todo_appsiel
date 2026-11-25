<?php

use App\FacturacionElectronica\OSEI\DocSoporte;
use App\FacturacionElectronica\OSEI\NotaAjusteDocSoporte;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class FakeDsNotaAjuste
{
    public $consecutivo = 106;
    public $fecha = '2026-05-10';
    public $resultados = [];

    public function resultados_envios_fe_doc_soporte()
    {
        return collect($this->resultados);
    }
}

class FakeNotaAjuste
{
    public $consecutivo = 3;
    public $fecha = '2026-06-01';
    public $fecha_vencimiento = '2026-06-01';
    public $forma_pago = 'contado';
    public $nota_ajuste_concepto = 1;
    public $descripcion = 'Devolucion parcial';
    public $tipo_documento_app;
    public $doc_soporte;
    public $proveedor;
    public $lineas_registros = [];

    public function __construct()
    {
        $this->tipo_documento_app = (object) ['prefijo' => 'NAS'];
        $this->doc_soporte = new FakeDsNotaAjuste();
        $this->proveedor = (object) ['tercero' => (object) [
            'id_tipo_documento_id' => 31, 'descripcion' => 'Proveedor SAS', 'numero_identificacion' => '900123456',
            'tipo' => 'Persona juridica', 'ciudad' => (object) ['id' => '16920001', 'descripcion' => 'Valledupar'],
            'direccion1' => 'Calle 1', 'codigo_postal' => '200001', 'email' => 'p@correo.com', 'telefono1' => '3001234567',
        ]];
    }

    public function get_label_documento()
    {
        return 'NAS 3';
    }

    public function agregarLinea($cantidad, $base, $tasa = 19)
    {
        $this->lineas_registros[] = (object) [
            'cantidad' => $cantidad, 'base_impuesto' => $base, 'tasa_impuesto' => $tasa,
            'item' => (object) ['id' => 7, 'unidad_medida1' => 'UND', 'descripcion' => 'Tornillo',
                'impuesto' => (object) ['tax_category' => 'IVA']],
        ];
    }
}

class NotaAjusteDocSoporteOseiTest extends TestCase
{
    private $history = [];

    protected function setUp()
    {
        parent::setUp();
        config([
            'facturacion_electronica.fe_ambiente' => 'PRODUCCION',
            'facturacion_electronica.url_documento_soporte' => 'https://osei.com.co/api/v1/support_documents',
            'facturacion_electronica.cantidadDecimales' => 2,
        ]);
    }

    private function nota($concepto = 1)
    {
        $nota = new FakeNotaAjuste();
        $nota->nota_ajuste_concepto = $concepto;
        $nota->doc_soporte->resultados = [(object) [
            'esValidoDian' => 1,
            'cufe' => str_repeat('a', 96),
            'nombre' => json_encode(['support_doc' => ['number' => 106, 'issue_date' => '10/05/2026', 'resolution' => ['prefix' => 'DS']]]),
        ]];

        return $nota;
    }

    private function documento($nota, array $responses = [])
    {
        $this->history = [];
        $stack = HandlerStack::create(new MockHandler($responses));
        $stack->push(\GuzzleHttp\Middleware::history($this->history));
        $client = new Client(['handler' => $stack]);

        return new class($nota, $client) extends NotaAjusteDocSoporte {
            private $http;

            public function __construct($nota, $http)
            {
                parent::__construct($nota);
                $this->http = $http;
            }

            protected function client()
            {
                return $this->http;
            }

            protected function descripcion_concepto($codigo)
            {
                return [1 => 'Devolucion de bienes', 3 => 'Descuento'][$codigo];
            }
        };
    }

    private function payload($nota)
    {
        $documento = $this->documento($nota);

        return $documento->armar_payload($documento->get_documento_relacionado());
    }

    public function test_el_json_de_concepto_1_trae_raiz_discrepancia_y_documento_relacionado_del_ds()
    {
        $nota = $this->nota(1);
        $nota->agregarLinea(-2, -100.00);

        $payload = $this->payload($nota);

        $this->assertSame(['support_doc_note'], array_keys($payload));
        $json = $payload['support_doc_note'];
        $this->assertSame('NAS', $json['prefix']);
        $this->assertSame(3, $json['number']);
        $this->assertSame('01/06/2026', $json['issue_date']);
        $this->assertSame('PRODUCCION', $json['env']);
        $this->assertSame(['code' => 1, 'description' => 'Devolucion de bienes: Devolucion parcial'], $json['discrepancy']);
        $this->assertSame(['prefix' => 'DS', 'number' => 106, 'cuds' => str_repeat('a', 96), 'issue_date' => '10/05/2026'], $json['related_document']);
        $this->assertSame('900123456', $json['supplier']['identification_number']);
        $this->assertArrayNotHasKey('retentions', $json);

        $item = $json['items'][0];
        $this->assertSame(2.0, $item['quantity']);
        $this->assertSame(50.0, $item['price']);
        $this->assertArrayNotHasKey('discount_rate', $item);
        $this->assertSame([['tax_rate' => 19, 'tax_category' => 'IVA']], $item['taxes']);
    }

    public function test_concepto_3_envia_cantidad_1_y_el_valor_absoluto_de_la_base()
    {
        $nota = $this->nota(3);
        $nota->agregarLinea(-5, -250.50);

        $item = $this->payload($nota)['support_doc_note']['items'][0];

        $this->assertSame(1.0, $item['quantity']);
        $this->assertSame(250.5, $item['price']);
    }

    public function test_la_descripcion_de_la_discrepancia_se_corta_a_500_caracteres()
    {
        $nota = $this->nota(1);
        $nota->descripcion = str_repeat('x', 800);
        $nota->agregarLinea(-1, -10);

        $this->assertSame(500, strlen($this->payload($nota)['support_doc_note']['discrepancy']['description']));
    }

    public function test_sin_resultado_valido_del_ds_devuelve_error_y_no_llama_a_osei()
    {
        $nota = $this->nota(1);
        $nota->doc_soporte->resultados = [(object) ['esValidoDian' => 0, 'cufe' => '', 'nombre' => '{}']];

        $resultado = $this->documento($nota)->procesar_envio_factura();

        $this->assertSame('mensaje_error', $resultado->tipo);
        $this->assertContains('no ha sido aceptado por la DIAN', $resultado->contenido);
        $this->assertCount(0, $this->history);
    }

    public function test_el_envio_va_a_adjustment_notes_con_la_raiz_support_doc_note()
    {
        $nota = $this->nota(1);
        $nota->agregarLinea(-2, -100);

        $documento = $this->documento($nota, [new Response(404, [], '{}')]);
        $resultado = $documento->procesar_envio_factura();

        $this->assertSame('mensaje_error', $resultado->tipo);
        $request = $this->history[0]['request'];
        $this->assertSame('/api/v1/support_documents/adjustment_notes', $request->getUri()->getPath());
        $cuerpo = json_decode((string) $request->getBody(), true);
        $this->assertSame(['support_doc_note'], array_keys($cuerpo));
        $this->assertSame('NAS', $documento->prefijo_osei());
    }

    public function test_el_documento_soporte_sigue_usando_support_doc_y_su_url()
    {
        $ds = new class(new FakeDsNotaAjuste(), 'support_doc') extends DocSoporte {
            public $log = [];

            protected function client()
            {
                $stack = HandlerStack::create(new MockHandler([new Response(404, [], '{}')]));
                $stack->push(\GuzzleHttp\Middleware::history($this->log));

                return new Client(['handler' => $stack]);
            }

            public function url()
            {
                return $this->url_emision;
            }

            public function raiz()
            {
                return $this->llave_raiz;
            }
        };

        $this->assertSame('https://osei.com.co/api/v1/support_documents', $ds->url());
        $this->assertSame('support_doc', $ds->raiz());

        $ds->enviar_documento_electronico('tok', ['support_doc' => ['env' => 'PRODUCCION']]);

        $this->assertSame('/api/v1/support_documents', $ds->log[0]['request']->getUri()->getPath());
    }
}
