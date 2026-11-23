<?php

use App\Http\Controllers\FacturacionElectronica\NotaAjusteDocSoporteController as Controlador;

class NotaAjusteDocSoporteControllerTest extends PHPUnit_Framework_TestCase
{
    public function test_arma_la_solicitud_ignorando_vacios_y_ceros()
    {
        $this->assertSame([5 => 2.5, 7 => 100.0], Controlador::armar_solicitud(1, ['5' => '2.5', '6' => '', '7' => '100', '8' => '0']));
    }

    public function test_anulacion_no_usa_lineas()
    {
        $this->assertSame([], Controlador::armar_solicitud(2, [5 => '3']));
    }

    public function test_sin_lineas_devuelve_vacio()
    {
        $this->assertSame([], Controlador::armar_solicitud(3, null));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_valor_no_numerico_es_error_de_usuario()
    {
        Controlador::armar_solicitud(3, [5 => 'abc']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_valor_negativo_es_error_de_usuario()
    {
        Controlador::armar_solicitud(1, [5 => '-1']);
    }
}
