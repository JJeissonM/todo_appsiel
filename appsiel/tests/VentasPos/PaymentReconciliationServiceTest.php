<?php

use App\VentasPos\Services\PaymentReconciliationService;

class PaymentReconciliationServiceTest extends PHPUnit_Framework_TestCase
{
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new PaymentReconciliationService();
    }

    public function test_descuenta_el_cambio_del_recaudo_en_efectivo()
    {
        $resultado = $this->service->normalizar_cambio_en_efectivo(
            '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$187500"}]',
            178600,
            8900
        );

        $lineas = json_decode($resultado['lineas_json'], true);

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals('$178600', $lineas[0]['valor']);
        $this->assertEquals(178600.0, $resultado['total_recaudos']);
    }

    public function test_no_modifica_un_recaudo_que_ya_esta_neto()
    {
        $json = '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$178600"}]';
        $resultado = $this->service->normalizar_cambio_en_efectivo($json, 178600, 8900);

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_no_modifica_historicos_cuyo_excedente_no_coincide_con_el_cambio()
    {
        $json = '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$187600"}]';
        $resultado = $this->service->normalizar_cambio_en_efectivo($json, 178600, 8900);

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_no_descuenta_cambio_de_una_transferencia_bancaria()
    {
        $json = '[{"teso_caja_id":"0-","teso_cuenta_bancaria_id":"4-Banco","valor":"$187500"}]';
        $resultado = $this->service->normalizar_cambio_en_efectivo($json, 178600, 8900);

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_reconoce_formato_colombiano_en_valores_historicos()
    {
        $resultado = $this->service->normalizar_cambio_en_efectivo(
            '[{"teso_caja_id":"1-Caja","teso_cuenta_bancaria_id":"0-","valor":"$187.500"}]',
            178600,
            8900
        );

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(178600.0, $resultado['total_recaudos']);
    }

    public function test_corrige_el_ajuste_despues_de_agregar_comision_datafono()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178570"},{"teso_motivo_id":"84-Comision datafono","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_datafono(
            $json,
            178595,
            0,
            5,
            84,
            true
        );

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(-25.0, $resultado['ajuste']);
        $this->assertEquals(8930.0, $resultado['valor_datafono']);
        $this->assertEquals(187500.0, $resultado['total_recaudos']);
    }

    public function test_reconstruye_linea_unica_marcada_como_comision_datafono()
    {
        $json = '[{"teso_medio_recaudo_id":"2-Tarjeta debito","teso_motivo_id":"84-Comision datafono","teso_caja_id":"0-","teso_cuenta_bancaria_id":"11-Banco","valor":"$54900"}]';

        $resultado = $this->service->reconstruir_recargo_porcentual_linea_unica(
            $json,
            54910,
            0,
            5,
            84,
            'Comision datafono',
            83,
            'Ventas de contado',
            true
        );

        $lineas = json_decode($resultado['lineas_json'], true);
        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(44.0, $resultado['ajuste']);
        $this->assertEquals(2746.0, $resultado['valor_recargo']);
        $this->assertEquals(57700.0, $resultado['total_recaudos']);
        $this->assertSame('83-Ventas de contado', $lineas[0]['teso_motivo_id']);
        $this->assertSame('$54954', $lineas[0]['valor']);
        $this->assertSame('84-Comision datafono', $lineas[1]['teso_motivo_id']);
        $this->assertSame('$2746', $lineas[1]['valor']);
    }

    public function test_no_reconstruye_recaudo_que_ya_tiene_venta_y_comision()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$54954"},{"teso_motivo_id":"84-Comision datafono","valor":"$2746"}]';

        $resultado = $this->service->reconstruir_recargo_porcentual_linea_unica(
            $json,
            54910,
            0,
            5,
            84,
            'Comision datafono',
            83,
            'Ventas de contado',
            true
        );

        $this->assertFalse($resultado['normalizado']);
        $this->assertSame($json, $resultado['lineas_json']);
    }

    public function test_corrige_comision_datafono_sobredimensionada_en_pago_mixto()
    {
        $json = '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"83-Ventas","teso_caja_id":"2-Caja","teso_cuenta_bancaria_id":"0-","valor":"$76900"},{"teso_medio_recaudo_id":"2-Tarjeta","teso_motivo_id":"84-Comision","teso_caja_id":"0-","teso_cuenta_bancaria_id":"11-Banco","valor":"$100000"}]';

        $resultado = $this->service->normalizar_lineas_recargo(
            $json,
            8422,
            84,
            'Comision datafono',
            83,
            'Ventas de contado'
        );

        $lineas = json_decode($resultado['lineas_json'], true);
        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(176900.0, $resultado['total_recaudos']);
        $this->assertCount(3, $lineas);
        $this->assertSame('$91578', $lineas[1]['valor']);
        $this->assertSame('83-Ventas de contado', $lineas[1]['teso_motivo_id']);
        $this->assertSame('11-Banco', $lineas[1]['teso_cuenta_bancaria_id']);
        $this->assertSame('$8422', $lineas[2]['valor']);
        $this->assertSame('84-Comision datafono', $lineas[2]['teso_motivo_id']);
    }

    public function test_completa_comision_datafono_deficitaria_desde_una_linea_normal()
    {
        $json = '[{"teso_medio_recaudo_id":"2-Tarjeta","teso_motivo_id":"83-Ventas","valor":"$99500"},{"teso_medio_recaudo_id":"2-Tarjeta","teso_motivo_id":"84-Comision","valor":"$500"}]';

        $resultado = $this->service->normalizar_lineas_recargo(
            $json,
            5000,
            84,
            'Comision datafono',
            83,
            'Ventas de contado'
        );

        $lineas = json_decode($resultado['lineas_json'], true);
        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(100000.0, $resultado['total_recaudos']);
        $this->assertEquals(5000.0, collect($lineas)->filter(function ($linea) {
            return strpos($linea['teso_motivo_id'], '84-') === 0;
        })->sum(function ($linea) {
            return (float)substr($linea['valor'], 1);
        }));
    }

    public function test_no_corrige_datafono_si_los_recaudos_no_coinciden_con_el_total_redondeado()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178595"},{"teso_motivo_id":"84-Comision datafono","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_datafono($json, 178595, 0, 5, 84, true);

        $this->assertFalse($resultado['normalizado']);
        $this->assertEquals(5.0, $resultado['ajuste']);
    }

    public function test_no_corrige_facturas_sin_motivo_datafono_configurado()
    {
        $json = '[{"teso_motivo_id":"84-Comision datafono","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_datafono($json, 178595, 0, 5, 0, true);

        $this->assertFalse($resultado['normalizado']);
        $this->assertEquals(0.0, $resultado['valor_datafono']);
    }

    public function test_no_modifica_ajuste_datafono_que_ya_es_correcto()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178570"},{"teso_motivo_id":"84-Comision datafono","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_datafono($json, 178595, 0, -25, 84, true);

        $this->assertFalse($resultado['normalizado']);
        $this->assertEquals(-25.0, $resultado['ajuste']);
    }

    public function test_calcula_ajuste_a_unidad_si_no_se_redondea_a_centena()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178595"},{"teso_motivo_id":"84-Comision datafono","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_datafono($json, 178594.6, 0, 5, 84, false);

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(0.4, $resultado['ajuste']);
    }

    public function test_corrige_ajuste_para_propina_manual()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178570"},{"teso_motivo_id":"85-Propina","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_recargos($json, 178595, 0, 5, [85], true);

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(-25.0, $resultado['ajuste']);
        $this->assertEquals(8930.0, $resultado['valor_recargos']);
        $this->assertEquals(8930.0, $resultado['valores_por_motivo'][85]);
    }

    public function test_calcula_un_solo_ajuste_para_propina_y_datafono()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178570"},{"teso_motivo_id":"85-Propina","valor":"$8900"},{"teso_motivo_id":"84-Comision datafono","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_recargos($json, 178595, 0, 5, [85, 84], true);

        $this->assertTrue($resultado['normalizado']);
        $this->assertEquals(-25.0, $resultado['ajuste']);
        $this->assertEquals(17830.0, $resultado['valor_recargos']);
        $this->assertEquals(196400.0, $resultado['total_recaudos']);
    }

    public function test_no_corrige_propina_historica_si_el_recaudo_no_cuadra_con_el_total_final()
    {
        $json = '[{"teso_motivo_id":"83-Ventas","valor":"$178595"},{"teso_motivo_id":"85-Propina","valor":"$8930"}]';

        $resultado = $this->service->normalizar_ajuste_recargos($json, 178595, 0, 5, [85], true);

        $this->assertFalse($resultado['normalizado']);
        $this->assertEquals(5.0, $resultado['ajuste']);
    }
}
