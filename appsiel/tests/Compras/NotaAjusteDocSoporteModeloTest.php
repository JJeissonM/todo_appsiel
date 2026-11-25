<?php

use App\Compras\ComprasDocRegistro;
use App\FacturacionElectronica\NotaAjusteDocSoporte;

class NotaAjusteDocSoporteModeloTest extends PHPUnit_Framework_TestCase
{
    public function test_constantes_de_transaccion()
    {
        $this->assertSame(62, NotaAjusteDocSoporte::TRANSACCION_ID);
        $this->assertSame(48, NotaAjusteDocSoporte::TRANSACCION_DOC_SOPORTE_ID);
    }

    public function test_conceptos_y_descripcion()
    {
        $this->assertSame([1, 2, 3, 4, 5], array_keys(NotaAjusteDocSoporte::CONCEPTOS));

        $nota = new NotaAjusteDocSoporte(['nota_ajuste_concepto' => 2]);
        $this->assertSame('Anulación del documento soporte', $nota->concepto_descripcion());
        $this->assertSame('', (new NotaAjusteDocSoporte(['nota_ajuste_concepto' => 9]))->concepto_descripcion());
    }

    public function test_fillable_hereda_del_padre_y_agrega_el_concepto()
    {
        $nota = new NotaAjusteDocSoporte(['nota_ajuste_concepto' => 3, 'compras_doc_relacionado_id' => 7]);

        $this->assertSame(3, $nota->nota_ajuste_concepto);
        $this->assertSame(7, $nota->compras_doc_relacionado_id);
        $this->assertSame('compras_doc_encabezados', $nota->getTable());
    }

    public function test_registro_acepta_la_linea_origen_en_fill()
    {
        $registro = new ComprasDocRegistro(['compras_doc_registro_origen_id' => 15]);

        $this->assertSame(15, $registro->compras_doc_registro_origen_id);
    }
}
