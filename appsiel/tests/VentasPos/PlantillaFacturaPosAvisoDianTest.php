<?php

use App\FacturacionElectronica\ResultadoEnvioDocumento;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PlantillaFacturaPosAvisoDianTest extends TestCase
{
    use DatabaseTransactions;

    const FACTURA_ELECTRONICA_ID = 990001;

    protected function setUp()
    {
        parent::setUp();
        config(['facturacion_electronica.proveedor_tecnologico_default' => 'OSEI']);
    }

    protected function renderTicket($reglasDian, $vista = 'plantilla_factura_default')
    {
        if ($reglasDian != '') {
            ResultadoEnvioDocumento::create([
                'vtas_doc_encabezado_id' => self::FACTURA_ELECTRONICA_ID,
                'codigo' => '99',
                'consecutivoDocumento' => 'FE51685',
                'cufe' => 'cufe-de-prueba',
                'esValidoDian' => 0,
                'fechaAceptacionDIAN' => '',
                'fechaRespuesta' => '2026-11-03 20:49:44',
                'reglasNotificacionDIAN' => $reglasDian,
                'resultado' => 'Rechazado por la DIAN',
                'nombre' => '{}',
            ]);
        }

        $tercero = (object)[
            'id' => 1,
            'descripcion' => 'TORNILLOS ATLANTICOS S.A.S',
            'numero_identificacion' => '900870770',
            'direccion1' => 'CL 30 25 37',
            'telefono1' => '3182657728',
        ];

        $doc_encabezado = (object)[
            'id' => 30245,
            'core_tercero_id' => 1,
            'tercero' => $tercero,
            'vendedor' => null,
            'pdv' => null,
            'estado' => 'Activo',
        ];

        return View::make('ventas_pos.formatos_impresion.' . $vista, [
            'empresa' => (object)['descripcion' => 'FOLDISA SAS', 'nombre1' => '', 'numero_identificacion' => '901798920', 'digito_verificacion' => '4', 'direccion1' => 'CRA 7A No. 28-79', 'telefono1' => '3046740643', 'email' => 'foldisasas@gmail.com', 'imagen' => ''],
            'resolucion' => null,
            'etiquetas' => ['encabezado' => '', 'pie_pagina' => ''],
            'pdv_descripcion' => 'PDV',
            'cliente' => null,
            'tipo_doc_app' => (object)['descripcion' => 'FACTURA ELECTRÓNICA DE VENTAS', 'prefijo' => 'FE'],
            'doc_encabezado' => $doc_encabezado,
            'electronic_invoice' => (object)['id' => self::FACTURA_ELECTRONICA_ID],
            'datos_factura' => (object)[
                'core_tipo_transaccion_id' => 52,
                'lbl_consecutivo_doc_encabezado' => '51685',
                'lbl_fecha' => '2026-11-03',
                'lbl_hora' => '03:08:49 pm',
                'lbl_condicion_pago' => 'contado',
                'lbl_fecha_vencimiento' => '2026-11-03',
                'lbl_descripcion_doc_encabezado' => '',
                'lbl_total_factura' => '36.500',
                'lbl_total_propina' => 0,
                'total_factura_mas_propina' => 36500,
                'lbl_total_datafono' => 0,
                'total_factura_mas_datafono' => 36500,
                'lbl_ajuste_al_peso' => 0,
                'lbl_total_recibido' => 0,
                'lbl_total_cambio' => 0,
                'lbl_valor_total_bolsas' => 0,
                'lbl_creado_por_fecha_y_hora' => '',
                'lineas_registros' => '<tr><td>TORNILLO (UND)</td><td>1 (36.500)</td><td>8%</td><td>36.500</td></tr>',
                'obj_lineas_registros' => [],
                'lineas_impuesto' => '',
                'saldo_pendiente_cxc' => 0,
            ],
        ])->render();
    }

    public function test_el_aviso_de_la_dian_se_ve_en_pantalla_pero_no_se_imprime()
    {
        $html = $this->renderTicket('Regla: RUT01, Rechazo: el estado de su RUT es 0.');

        $this->assertContains('<div class="aviso-envio-dian"', $html);
        $this->assertContains('Factura no fue Enviada hacia la DIAN', $html);
        $this->assertContains('Regla: RUT01', $html);
        // Blade 5.2 duplica el espacio tras "@media": queda "@media  print", CSS válido.
        $this->assertRegExp('/@media\s+print\s*\{\s*\.aviso-envio-dian\s*\{\s*display:\s*none !important;/', $html);
    }

    public function test_sin_rechazo_no_hay_aviso_y_el_ticket_sale_completo()
    {
        $html = $this->renderTicket('');

        $this->assertNotContains('<div class="aviso-envio-dian"', $html);
        $this->assertContains('VENTAS No.', $html);
        $this->assertContains('TORNILLOS ATLANTICOS S.A.S', $html);
        $this->assertContains('TORNILLO (UND)', $html);
    }
}
