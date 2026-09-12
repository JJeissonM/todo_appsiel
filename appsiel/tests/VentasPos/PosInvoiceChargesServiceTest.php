<?php

use App\FacturacionElectronica\Services\PosInvoiceChargesService;
use App\VentasPos\FacturaPos;
use App\Ventas\VtasDocEncabezado;

class TestablePosInvoiceChargesService extends PosInvoiceChargesService
{
    public $pos;
    protected function findPosInvoice($invoice) { return $this->pos; }
}

class PosInvoiceChargesServiceTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['ventas_pos.manejar_propinas' => 1, 'ventas_pos.manejar_datafono' => 1,
            'ventas_pos.motivo_tesoreria_propinas' => 85, 'ventas_pos.motivo_tesoreria_datafono' => 84,
            'facturacion_electronica.proveedor_tecnologico_default' => 'DATAICO']);
    }

    public function test_combinaciones_de_recargos_y_redondeos_cuadran_con_el_pago()
    {
        foreach ([0, 8900] as $tip) {
            foreach ([0, 8930] as $fee) {
                foreach ([-25, 0, 25] as $adjustment) {
                    list($service, $invoice) = $this->scenario($tip, $fee, $adjustment);
                    $charges = $service->getCharges($invoice);
                    $total = $invoice->valor_total;
                    foreach ($charges as $charge) {
                        $this->assertGreaterThan(0, $charge['base_amount']);
                        $total += $charge['base_amount'] * ($charge['discount'] ? -1 : 1);
                    }
                    $this->assertEquals(178595 + $tip + $fee + $adjustment, $total);
                    $this->assertSame($charges, $service->getCharges($invoice));
                }
            }
        }
    }

    public function test_no_envia_recargos_que_no_cuadran_con_los_pagos()
    {
        list($service, $invoice) = $this->scenario(8900, 8930, -25);
        $invoice->valor_ajuste_al_peso = 5;
        $this->setExpectedException('UnexpectedValueException');
        $service->getCharges($invoice);
    }

    public function test_no_omite_cargos_con_proveedor_sin_soporte()
    {
        list($service, $invoice) = $this->scenario(8900, 8930, -25);
        config(['facturacion_electronica.proveedor_tecnologico_default' => 'TFHKA']);
        $this->setExpectedException('UnexpectedValueException');
        $service->getCharges($invoice);
    }

    private function scenario($tip, $fee, $adjustment)
    {
        $invoice = new VtasDocEncabezado();
        $invoice->valor_total = 178595;
        $invoice->valor_ajuste_al_peso = $adjustment;
        $pos = new FacturaPos();
        $pos->forma_pago = 'contado';
        $pos->lineas_registros_medios_recaudos = json_encode([
            ['teso_motivo_id' => '83-Venta', 'valor' => '$' . (178595 + $adjustment)],
            ['teso_motivo_id' => '85-Propina', 'valor' => '$' . $tip],
            ['teso_motivo_id' => '84-Comision', 'valor' => '$' . $fee]
        ]);
        $service = new TestablePosInvoiceChargesService();
        $service->pos = $pos;
        return [$service, $invoice];
    }
}
