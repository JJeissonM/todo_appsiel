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
}
