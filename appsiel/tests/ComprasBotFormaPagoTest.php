<?php

use App\Compras\Services\SyncFacturaCompraService;

class ComprasBotFormaPagoTest extends PHPUnit_Framework_TestCase
{
    /** @dataProvider formasValidas */
    public function test_interpreta_la_forma_de_pago_sin_confundir_el_medio($valor, $esperado)
    {
        $this->assertSame($esperado, $this->interpretar($valor));
    }

    public function formasValidas()
    {
        return [
            ['CONTADO', 'contado'], [' contado ', 'contado'],
            ['DEBITO', 'contado'], [' débito ', 'contado'],
            ['DÉBITO', 'contado'], ['CREDITO', 'credito'],
            [' crédito ', 'credito'], ['CRÉDITO', 'credito'],
        ];
    }

    /** @dataProvider formasInvalidas */
    public function test_no_asigna_credito_por_defecto_a_datos_ambiguos($valor)
    {
        $this->setExpectedException('InvalidArgumentException', 'payment_means_type');
        $this->interpretar($valor);
    }

    public function formasInvalidas()
    {
        return [[null], [''], ['   '], ['EFECTIVO'], ['TRANSFERENCIA'], ['OTRO'], [1], ['2'], [[]]];
    }

    private function interpretar($valor)
    {
        $metodo = new ReflectionMethod(SyncFacturaCompraService::class, 'parsear_forma_pago');
        $metodo->setAccessible(true);
        return $metodo->invoke(new SyncFacturaCompraService(), $valor);
    }
}
