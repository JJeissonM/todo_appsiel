<?php

use App\Contabilidad\Services\EstructuraEeffService;

class EstructuraEeffTest extends PHPUnit_Framework_TestCase
{
    private function grupos()
    {
        return [
            (object)['id'=>5,'grupo_padre_id'=>0,'contab_cuenta_clase_id'=>1,'descripcion'=>'PPE'],
            (object)['id'=>49,'grupo_padre_id'=>5,'contab_cuenta_clase_id'=>1,'descripcion'=>'TERRENOS'],
            (object)['id'=>63,'grupo_padre_id'=>49,'contab_cuenta_clase_id'=>1,'descripcion'=>'VALORIZACIONES'],
        ];
    }

    private function movimiento($codigo, $grupo, $valor)
    {
        return (object)['cuenta_codigo'=>$codigo,'cuenta_descripcion'=>'Cuenta','cuenta_clase_id'=>1,'cuenta_clase_descripcion'=>'Activos','cuenta_grupo_hijo_id'=>$grupo,'valor_saldo'=>$valor];
    }

    public function testAcumulaTresNivelesSinDuplicarCuentasNiSaldos()
    {
        $service = new EstructuraEeffService();
        $rows = $service->generar([$this->movimiento('150405',49,100),$this->movimiento('191004',63,20),$this->movimiento('191008',63,-3)],$this->grupos(),[1],true);
        $this->assertEquals(117, $service->totales_clases[1]);
        $this->assertEquals(117, $rows[1]->datos_grupo_padre->valor);
        $this->assertEquals('TERRENOS', $rows[2]->datos_grupo_hijo->descripcion);
        $this->assertEquals(117, $rows[2]->datos_grupo_hijo->valor);
        $this->assertEquals(17, $rows[4]->datos_grupo_hijo->valor);
        $this->assertEquals(3, $rows[4]->nivel);
        $this->assertEquals(4, $rows[5]->nivel);
        $this->assertCount(7, $rows);
    }

    public function testMoverElGrupoCambiaSuUbicacionYConservaElTotal()
    {
        $grupos = $this->grupos();
        $grupos[2]->grupo_padre_id = 5;
        $service = new EstructuraEeffService();
        $rows = $service->generar([$this->movimiento('150405',49,100),$this->movimiento('191004',63,20)],$grupos,[1],false);
        $this->assertCount(4,$rows);
        $this->assertEquals(120,$rows[1]->datos_grupo_padre->valor);
        $this->assertEquals(100,$rows[2]->datos_grupo_hijo->valor);
        $this->assertEquals(2,$rows[3]->nivel);
        $this->assertEquals(20,$rows[3]->datos_grupo_hijo->valor);
    }

    public function testRechazaCiclosEnLaJerarquia()
    {
        $grupos = $this->grupos();
        $grupos[0]->grupo_padre_id = 63;
        $this->setExpectedException(RuntimeException::class);
        (new EstructuraEeffService())->generar([$this->movimiento('191004',63,20)],$grupos,[1],false);
    }

    public function testRechazaPadresInexistentes()
    {
        $grupos = $this->grupos();
        $grupos[1]->grupo_padre_id = 999;
        $this->setExpectedException(RuntimeException::class);
        (new EstructuraEeffService())->generar([$this->movimiento('191004',63,20)],$grupos,[1],false);
    }
}
