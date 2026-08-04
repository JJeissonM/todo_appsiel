<?php

use App\Nomina\NomConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\NominaElectronica\ConceptoDian;
use App\NominaElectronica\DATAICO\Services\DocumentoSoporteService;
use Illuminate\Support\Collection;

class DocumentoSoporteServiceTest extends TestCase
{
    /** @test */
    public function el_sueldo_no_reporta_mas_de_treinta_dias()
    {
        $conceptoDian = new ConceptoDian();
        $conceptoDian->codigo = 'BASICO';
        $conceptoDian->liquida_dias = true;
        $conceptoDian->liquida_horas = false;
        $conceptoDian->porcentaje_del_basico = 0;
        $conceptoDian->tipo_concepto = 'amount';

        $concepto = new NomConcepto();
        $concepto->descripcion = 'SUELDO';
        $concepto->modo_liquidacion_id = 1;
        $concepto->setRelation('cpto_dian', $conceptoDian);

        $registro = new NomDocRegistro();
        $registro->cantidad_horas = 320;
        $registro->setRelation('concepto', $concepto);
        $registros = new Collection([$registro]);

        $linea = (new DocumentoSoporteService())->get_linea_empleado(
            $registros,
            $concepto,
            2000000,
            $registros,
            8
        );

        $this->assertSame(30, $linea['days']);
        $this->assertSame(2000000, $linea['amount']);
    }
}
