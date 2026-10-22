<?php

use App\FacturacionElectronica\OSEI\FacturaGeneralOsei;
use App\VentasPos\Services\ElectronicInvoiceSendingService;

class FakeDocEncabezadoErrorResolucion
{
    public $id = 519;
    public $fecha = '2026-10-22';
    public $resolucion;

    public function resolucion_facturacion()
    {
        return $this->resolucion;
    }
}

class FacturaGeneralOseiErrorResolucion extends FacturaGeneralOsei
{
    public function mensajeErrorResolucion($responseBody)
    {
        return $this->resolutionErrorMessage($responseBody);
    }
}

class FacturaGeneralOseiResolutionErrorTest extends TestCase
{
    protected function respuestaOsei(array $overrides = [])
    {
        return json_encode(array_merge([
            'status' => 'error',
            'error_code' => 'RESOLUCION_PREFIJO_NO_ASOCIADO',
            'error_type' => 'resolution',
            'message' => 'La DIAN no tiene asociado el prefijo APSI de la resolución 18764099722901 al software de facturación electrónica, por eso no puede validar la factura. Ingrese al portal de Facturación Electrónica de la DIAN, en Rangos de numeración asocie el prefijo APSI al software de facturación y vuelva a enviar la factura.',
            'action' => 'Ingrese al portal de Facturación Electrónica de la DIAN, en Rangos de numeración asocie el prefijo APSI al software de facturación y vuelva a enviar la factura.',
            'resolution' => ['number' => '18764099722901', 'prefix' => 'APSI'],
            'dian' => [
                'operation_code' => '302',
                'description' => 'No registra prefijos asociados al código de software: 34883bf6-c4d2-46f4-bbcd-0eea2120c838.',
            ],
        ], $overrides));
    }

    protected function factura($fecha_expiracion = '2027-10-21')
    {
        $doc = new FakeDocEncabezadoErrorResolucion();
        $doc->resolucion = (object)[
            'numero_resolucion' => '18764099722901',
            'prefijo' => 'APSI',
            'fecha_expedicion' => '2026-10-21',
            'fecha_expiracion' => $fecha_expiracion,
            'numero_fact_inicial' => 789,
            'numero_fact_final' => 10000,
        ];

        return new FacturaGeneralOseiErrorResolucion($doc, 'factura');
    }

    public function test_muestra_el_error_de_resolucion_con_la_resolucion_de_appsiel()
    {
        $contenido = $this->factura()->mensajeErrorResolucion($this->respuestaOsei());

        $this->assertContains('hay un problema con la resolución de facturación', $contenido);
        $this->assertContains('asocie el prefijo APSI al software', $contenido);
        $this->assertContains('Resolución configurada en Appsiel: N° 18764099722901, prefijo APSI, vigente del 21/10/2026 al 21/10/2027, consecutivos 789 a 10000.', $contenido);
        $this->assertContains('Respuesta de la DIAN: No registra prefijos asociados', $contenido);
        $this->assertNotContains('ya venció', $contenido);
    }

    public function test_avisa_cuando_la_resolucion_de_appsiel_esta_vencida()
    {
        $contenido = $this->factura('2026-10-06')->mensajeErrorResolucion($this->respuestaOsei());

        $this->assertContains('Esta resolución ya venció', $contenido);
    }

    public function test_ignora_los_errores_que_no_son_de_la_resolucion()
    {
        $factura = $this->factura();

        $this->assertNull($factura->mensajeErrorResolucion(json_encode(['status' => 'error', 'message' => 'Cuenta Invalida.'])));
        $this->assertNull($factura->mensajeErrorResolucion($this->respuestaOsei(['error_type' => 'dian'])));
        $this->assertNull($factura->mensajeErrorResolucion('<html>Server Error</html>'));
    }

    public function test_escapa_el_html_que_venga_de_osei()
    {
        $contenido = $this->factura()->mensajeErrorResolucion($this->respuestaOsei(['message' => '<script>x</script>']));

        $this->assertNotContains('<script>', $contenido);
        $this->assertContains('&lt;script&gt;', $contenido);
    }

    public function test_el_pos_no_reintenta_un_error_de_resolucion()
    {
        $contenido = $this->factura('2026-10-06')->mensajeErrorResolucion($this->respuestaOsei());

        $this->assertFalse((new ElectronicInvoiceSendingService())->isRetryableError($contenido));
    }
}
