<?php

use App\Nomina\NomContrato;

class NomContratoTest extends TestCase
{
    /** @test */
    public function los_registros_de_nomina_se_relacionan_por_contrato()
    {
        $contrato = new NomContrato();
        $contrato->id = 123;
        $contrato->core_tercero_id = 456;

        $relacion = $contrato->registros_documentos_nomina();

        $this->assertSame('nom_doc_registros.nom_contrato_id', $relacion->getForeignKey());
        $this->assertSame(123, $relacion->getParentKey());
    }

    /** @test */
    public function excluye_salud_y_pension_solo_cuando_la_entidad_del_contrato_coincide()
    {
        config(['nomina.entidad_excluyente_aportes_id' => '25']);

        $contrato = new NomContrato();
        $contrato->entidad_salud_id = 25;
        $contrato->entidad_pension_id = 30;

        $this->assertTrue($contrato->excluye_aporte_obligatorio(12));
        $this->assertFalse($contrato->excluye_aporte_obligatorio(13));

        $contrato->entidad_salud_id = 30;
        $contrato->entidad_pension_id = 25;

        $this->assertFalse($contrato->excluye_aporte_obligatorio(12));
        $this->assertTrue($contrato->excluye_aporte_obligatorio(13));
        $this->assertFalse($contrato->excluye_aporte_obligatorio(10));
    }

    /** @test */
    public function no_excluye_aportes_si_no_hay_entidad_configurada()
    {
        config(['nomina.entidad_excluyente_aportes_id' => '']);

        $contrato = new NomContrato();
        $contrato->entidad_salud_id = 25;
        $contrato->entidad_pension_id = 25;

        $this->assertFalse($contrato->excluye_aporte_obligatorio(12));
        $this->assertFalse($contrato->excluye_aporte_obligatorio(13));
    }
}
