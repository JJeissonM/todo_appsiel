<?php

use App\VentasPos\Services\PaymentReconciliationService;

class PaymentReconciliationServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new PaymentReconciliationService();
    }

    public function test_descuenta_el_cambio_del_recaudo_en_efectivo()
    {
        $resultado = $this->service->normalizar_cambio_en_efectivo(
            '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$187500"}]',
            178600,
            8900
        );

        $lineas = json_decode($resultado['lineas_json'], true);

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals('$178600', $lineas[0]['valor']);
        $this->assertEquals(178600.0, $resultado['total_recaudos']);
    }

    public function test_no_modifica_un_recaudo_que_ya_esta_neto()
    {
        $json = '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$178600"}]';
        $resultado = $this->service->normalizar_cambio_en_efectivo($json, 178600, 8900);

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_no_modifica_historicos_cuyo_excedente_no_coincide_con_el_cambio()
    {
        $json = '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$187600"}]';
        $resultado = $this->service->normalizar_cambio_en_efectivo($json, 178600, 8900);

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_no_descuenta_cambio_de_una_transferencia_bancaria()
    {
        $json = '[{"teso_caja_id":"0-","teso_cuenta_bancaria_id":"4-Banco","valor":"$187500"}]';
        $resultado = $this->service->normalizar_cambio_en_efectivo($json, 178600, 8900);

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_reconoce_formato_colombiano_en_valores_historicos()
    {
        $resultado = $this->service->normalizar_cambio_en_efectivo(
            '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$187.500"}]',
            178600,
            8900
        );

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(178600.0, $resultado['total_recaudos']);
    }
}
