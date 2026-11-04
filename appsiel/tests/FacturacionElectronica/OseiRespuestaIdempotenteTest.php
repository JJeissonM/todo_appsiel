<?php

use App\FacturacionElectronica\OSEI\FacturaGeneralOsei;
use App\FacturacionElectronica\ResultadoEnvioDocumento;
use App\VentasPos\Services\ElectronicInvoiceSendingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OseiRespuestaIdempotenteTest extends TestCase
{
    use DatabaseTransactions;

    const FACTURA_ID = 990001;

    protected function factura()
    {
        $class = new ReflectionClass(FacturaGeneralOsei::class);
        $factura = $class->newInstanceWithoutConstructor();

        $property = $class->getProperty('doc_encabezado');
        $property->setAccessible(true);
        $property->setValue($factura, (object)['id' => self::FACTURA_ID]);

        return $factura;
    }

    protected function invocar($factura, $metodo, array $argumentos = [])
    {
        $method = new ReflectionMethod(FacturaGeneralOsei::class, $metodo);
        $method->setAccessible(true);

        return $method->invokeArgs($factura, $argumentos);
    }

    public function test_muestra_el_ultimo_rechazo_guardado_cuando_osei_no_devuelve_el_resultado()
    {
        ResultadoEnvioDocumento::create([
            'vtas_doc_encabezado_id' => self::FACTURA_ID,
            'codigo' => '99',
            'esValidoDian' => 0,
            'fechaAceptacionDIAN' => '',
            'fechaRespuesta' => '2026-11-03 20:30:34',
            'reglasNotificacionDIAN' => "Regla: RUT01, Rechazo: estado del RUT 0\nRegla: FAX14, Notificación: tarifa <distinta>",
            'resultado' => 'Rechazado por la DIAN',
            'nombre' => '{}',
        ]);

        $contenido = $this->invocar($this->factura(), 'idempotentResponseMessage', [['idempotent' => true, 'existing_id' => 16471]]);

        $this->assertContains('Este documento ya se había enviado a la DIAN y fue rechazado.', $contenido);
        $this->assertContains('Último resultado de la DIAN (2026-11-03 20:30:34)', $contenido);
        $this->assertContains('Regla: RUT01, Rechazo: estado del RUT 0<br />', $contenido);
        $this->assertContains('tarifa &lt;distinta&gt;', $contenido);
    }

    public function test_sin_resultado_guardado_explica_que_osei_no_devolvio_el_resultado()
    {
        $contenido = $this->invocar($this->factura(), 'idempotentResponseMessage', [['idempotent' => true, 'existing_id' => 16471]]);

        $this->assertContains('OSEI ya había recibido este mismo documento (registro N° 16471)', $contenido);
        $this->assertNotContains('zip_key', $contenido);
    }

    public function test_el_pos_no_reintenta_un_reenvio_ya_rechazado()
    {
        $aviso = $this->invocar($this->factura(), 'avisoReenvioSinCambios');

        $this->assertFalse((new ElectronicInvoiceSendingService())->isRetryableError($aviso));
    }
}
