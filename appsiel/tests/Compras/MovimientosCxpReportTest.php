<?php

use App\Http\Controllers\Compras\ReportesController;

class MovimientosCxpReportTest extends TestCase
{
    public function test_ordena_movimientos_de_la_misma_fecha_y_calcula_un_solo_saldo_acumulado()
    {
        $data = collect([
            $this->linea('2026-08-20', 'Pago', 0, 300, '2026-08-20 10:00:00', 2, 8),
            $this->linea('2026-08-19', 'Saldo anterior', 1000, 0, '2026-08-19 00:00:00', 0, 0),
            $this->linea('2026-08-20', 'Factura', 500, 0, '2026-08-20 09:00:00', 1, 15)
        ]);

        $resultado = (new ReportesController())
            ->ordenar_y_calcular_saldos_cxp($data);

        $this->assertSame(
            ['Saldo anterior', 'Factura', 'Pago'],
            $resultado->pluck('documento')->all()
        );
        $this->assertSame(1000.0, $resultado[0]->valor_saldo);
        $this->assertSame(1500.0, $resultado[1]->valor_saldo);
        $this->assertSame(1200.0, $resultado[2]->valor_saldo);
    }

    public function test_usa_tipo_e_id_como_desempate_estable()
    {
        $momento = '2026-08-20 09:00:00';
        $data = collect([
            $this->linea('2026-08-20', 'Pago', 0, 100, $momento, 2, 1),
            $this->linea('2026-08-20', 'Factura 2', 200, 0, $momento, 1, 2),
            $this->linea('2026-08-20', 'Factura 1', 300, 0, $momento, 1, 1)
        ]);

        $resultado = (new ReportesController())
            ->ordenar_y_calcular_saldos_cxp($data);

        $this->assertSame(
            ['Factura 1', 'Factura 2', 'Pago'],
            $resultado->pluck('documento')->all()
        );
        $this->assertSame(400.0, $resultado->last()->valor_saldo);
    }

    private function linea($fecha, $documento, $cartera, $aFavor, $momento, $tipo, $id)
    {
        return (object)[
            'fecha' => $fecha,
            'documento' => $documento,
            'valor_cartera' => $cartera,
            'valor_a_favor' => $aFavor,
            'valor_saldo' => 0,
            'orden_momento' => $momento,
            'orden_tipo' => $tipo,
            'orden_id' => $id
        ];
    }
}
