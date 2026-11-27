<?php

use App\Nomina\Services\ContabilizacionPilaNomina;

class ContabilizacionPilaNominaTest extends PHPUnit_Framework_TestCase
{
    protected function calcularValorPendiente($totalPila, $valorCausadoNomina)
    {
        $servicio = new ContabilizacionPilaNomina('2026-07-30', 15);
        $metodo = new ReflectionMethod(ContabilizacionPilaNomina::class, 'calcularValorPilaPendiente');
        $metodo->setAccessible(true);

        return $metodo->invoke($servicio, $totalPila, $valorCausadoNomina);
    }

    public function testContabilizaLaCorreccionDeSaludRegistradaEnLaPila()
    {
        $this->assertSame(49469.0, $this->calcularValorPendiente(490700, 441231));
    }

    public function testContabilizaElAportePatronalUsandoElTotalVigenteDeLaPila()
    {
        $this->assertSame(85000.0, $this->calcularValorPendiente(125000, 40000));
    }

    public function testNoDuplicaUnValorYaCausadoCompletamenteEnNomina()
    {
        $this->assertSame(0, $this->calcularValorPendiente(40000, 40000));
    }

    public function testNoGeneraUnaCuentaPorPagarNegativa()
    {
        $this->assertSame(0, $this->calcularValorPendiente(39000, 40000));
    }
}
