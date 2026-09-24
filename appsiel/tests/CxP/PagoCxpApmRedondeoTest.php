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

    public function testDiferenciaDeUnPesoPorRedondeoSeDistribuyeEnPesos()
    {
        $summary = $this->resumen([[6309460.30, 0], [6309460.30, 0], [0, -12618920.60]]);
        $this->assertSame(0, $summary['decimals']);
        $this->assertEquals(12618921, $summary['total_debit']);
        $this->assertSame($summary['total_debit'], $summary['total_credit']);
        $this->assertSame('$6,309,461', $summary['items'][0]['Debit']);
        $this->assertSame('$12,618,921', $summary['items'][2]['Credit']);
    }

    public function testRedondeoHaciaArribaTambienCuadraEnPesos()
    {
        $summary = $this->resumen([[0.5, 0], [0.5, 0], [0, -1]]);
        $this->assertSame(0, $summary['decimals']);
        $this->assertSame('$1', $summary['items'][0]['Debit']);
        $this->assertSame('$1', $summary['items'][2]['Credit']);
        $this->assertEquals(1, $summary['total_debit']);
        $this->assertSame('$0', $summary['items'][1]['Debit']);
    }

    public function testMantieneFormatoSinDecimalesCuandoCuadra()
    {
        $summary = $this->resumen([[125.5, 0], [0, -125.5]]);
        $this->assertSame(0, $summary['decimals']);
        $this->assertSame('$126', $summary['items'][0]['Debit']);
        $this->assertSame(126, $summary['total_credit']);
    }

    public function testElTotalEsElImporteRedondeadoInclusoSiAmbasColumnasRedondeabanIgual()
    {
        $summary = $this->resumen([[1.3, 0], [1.3, 0], [0, -1.3], [0, -1.3]]);
        $this->assertSame(3, $summary['total_debit']);
        $this->assertSame(3, $summary['total_credit']);
        $this->assertSame('$2', $summary['items'][0]['Debit']);
        $this->assertSame('$1', $summary['items'][1]['Debit']);
        $this->assertSame('$2', $summary['items'][2]['Credit']);
    }

    public function testConsolidaTodosLosMovimientosPosterioresAlOctavoEnUnaSolaLinea()
    {
        $summary = $this->resumen([
            [100, 0], [100, 0], [100, 0], [100, 0],
            [0, -100], [0, -100], [0, -100], [0, -100],
            [10.40, 0], [20.40, 0], [0, -15.40], [0, -15.40]
        ]);

        $this->assertCount(9, $summary['items']);
        $this->assertSame('...', $summary['items'][8]['Account']);
        $this->assertSame('$31', $summary['items'][8]['Debit']);
        $this->assertSame('$31', $summary['items'][8]['Credit']);
        $this->assertSame(431, $summary['total_debit']);
        $this->assertSame($summary['total_debit'], $summary['total_credit']);

        $debitosImpresos = array_sum(array_map(function ($item) {
            return (int)str_replace(['$', ','], '', $item['Debit']);
        }, $summary['items']));
        $creditosImpresos = array_sum(array_map(function ($item) {
            return (int)str_replace(['$', ','], '', $item['Credit']);
        }, $summary['items']));

        $this->assertSame($summary['total_debit'], $debitosImpresos);
        $this->assertSame($summary['total_credit'], $creditosImpresos);
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
