<?php

class PosKitchenPrinterConfigTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config([
            'ventas.metodo_impresion_pedido_ventas' => 'normal',
            'ventas.metodo_impresion_pedido_restaurante' => 'normal',
            'ventas.metodo_impresion_factura_pos' => 'normal',
            'ventas_pos.apm_scale_listener_activo' => 0,
            'tesoreria.usar_apm_pago_cxp' => 0
        ]);
    }

    public function test_jspm_toma_la_impresora_de_configuracion_ventas_pos()
    {
        config(['ventas_pos.impresora_cocina_por_defecto' => 'COCINA LOCAL']);
        $document = $this->renderFields();
        $this->assertSame('COCINA LOCAL', $document->getElementById('jspm_impresora_cocina_por_defecto')->getAttribute('value'));
        $this->assertSame('APM-PDV', $document->getElementById('impresora_cocina_por_defecto')->getAttribute('value'));
    }

    public function test_configuracion_vacia_no_reemplaza_la_impresora_jspm_con_el_dispositivo_apm()
    {
        config(['ventas_pos.impresora_cocina_por_defecto' => '']);
        $document = $this->renderFields();
        $this->assertSame('', $document->getElementById('jspm_impresora_cocina_por_defecto')->getAttribute('value'));
    }

    public function test_pedido_pos_carga_jspm_segun_su_metodo_independiente_de_restaurante()
    {
        config(['ventas.metodo_impresion_pedido_restaurante' => 'apm']);
        $data = ['params_JSPrintManager' => (object)['usar_complemento_JSPrintManager' => 1], 'metodo_impresion_jspm' => 'normal'];
        $this->assertContains('jspm_client.js', view('ventas_pos.formatos_impresion.scripts_comanda_jspm', $data)->render());
        $data['metodo_impresion_jspm'] = 'apm';
        $this->assertNotContains('jspm_client.js', view('ventas_pos.formatos_impresion.scripts_comanda_jspm', $data)->render());
    }

    protected function renderFields()
    {
        $params = (object)[
            'usar_complemento_JSPrintManager' => 1,
            'enviar_impresion_directamente_a_la_impresora' => 1,
            'imprimir_factura_automaticamente' => 0,
            'impresora_principal_por_defecto' => 'CAJA-PDV',
            'impresora_cocina_por_defecto' => 'APM-PDV'
        ];
        $html = view('ventas_pos.formatos_impresion.campos_adicionales_usar_JSPrintManager', ['params_JSPrintManager' => $params])->render();
        $document = new DOMDocument();
        $document->loadHTML($html);
        return $document;
    }
}
