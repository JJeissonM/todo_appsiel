<?php

use App\Http\Controllers\Tesoreria\PagoCxpController;
use Illuminate\Support\Collection;

class PagoCxpApmRedondeoTest extends PHPUnit_Framework_TestCase
{
    protected function resumen(array $valores)
    {
        $movimientos = array_map(function ($valor) {
            return (object) [
                'valor_debito' => $valor[0], 'valor_credito' => $valor[1],
                'cuenta' => (object) ['codigo' => '2205'],
                'tercero' => (object) ['numero_identificacion' => '123']
            ];
        }, $valores);
        $metodo = new ReflectionMethod(PagoCxpController::class, 'build_apm_accounting_summary_from_movements');
        $metodo->setAccessible(true);
        return $metodo->invoke(new PagoCxpController(), $movimientos, new Collection());
    }

    public function testDiferenciaDeUnPesoPorRedondeoConservaLosCentavos()
    {
        $summary = $this->resumen([[6309460.30, 0], [6309460.30, 0], [0, -12618920.60]]);
        $this->assertSame(2, $summary['decimals']);
        $this->assertEquals(12618920.60, $summary['total_debit']);
        $this->assertSame($summary['total_debit'], $summary['total_credit']);
        $this->assertSame('$6,309,460.30', $summary['items'][0]['Debit']);
        $this->assertSame('$12,618,920.60', $summary['items'][2]['Credit']);
    }

    public function testRedondeoHaciaArribaTambienConservaCentavos()
    {
        $summary = $this->resumen([[0.5, 0], [0.5, 0], [0, -1]]);
        $this->assertSame(2, $summary['decimals']);
        $this->assertSame('$0.50', $summary['items'][0]['Debit']);
        $this->assertSame('$1.00', $summary['items'][2]['Credit']);
        $this->assertEquals(1, $summary['total_debit']);
    }

    public function testMantieneFormatoSinDecimalesCuandoCuadra()
    {
        $summary = $this->resumen([[125.5, 0], [0, -125.5]]);
        $this->assertSame(0, $summary['decimals']);
        $this->assertSame('$126', $summary['items'][0]['Debit']);
        $this->assertSame(126, $summary['total_credit']);
    }

    /** @dataProvider descuadres */
    public function testRechazaDescuadresRealesAunqueElRedondeoLosOculte($debit, $credit)
    {
        $this->setExpectedException('RuntimeException', 'movimientos contables están descuadrados');
        $this->resumen([[$debit, 0], [0, $credit]]);
    }

    public function descuadres()
    {
        return [[100, -101], [100.10, -100.20]];
    }
}
