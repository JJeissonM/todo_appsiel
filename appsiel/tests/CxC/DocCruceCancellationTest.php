<?php

use App\CxC\CxcMovimiento;
use App\Http\Controllers\CxC\DocCruceController;

class DocCruceCancellationTest extends TestCase
{
    public function test_reversa_un_abono_sobre_cartera_parcialmente_pagada()
    {
        $movimiento = new CxcMovimiento();
        $movimiento->valor_pagado = 60;
        $movimiento->saldo_pendiente = 40;

        $valores = (new DocCruceController())
            ->calcular_reversion_mov_cxc($movimiento, 'cartera', 25);

        $this->assertSame(35.0, $valores['valor_pagado']);
        $this->assertSame(65.0, $valores['saldo_pendiente']);
        $this->assertSame('Pendiente', $valores['estado']);
    }

    public function test_reversa_un_abono_sobre_anticipo_negativo()
    {
        $movimiento = new CxcMovimiento();
        $movimiento->valor_pagado = -60;
        $movimiento->saldo_pendiente = -40;

        $valores = (new DocCruceController())
            ->calcular_reversion_mov_cxc($movimiento, 'afavor', 25);

        $this->assertSame(-35.0, $valores['valor_pagado']);
        $this->assertSame(-65.0, $valores['saldo_pendiente']);
        $this->assertSame('Pendiente', $valores['estado']);
    }

    public function test_reversion_total_normaliza_centavos_y_restaura_saldo_pendiente()
    {
        $movimiento = new CxcMovimiento();
        $movimiento->valor_pagado = 20.00001;
        $movimiento->saldo_pendiente = 0;

        $valores = (new DocCruceController())
            ->calcular_reversion_mov_cxc($movimiento, 'cartera', 20);

        $this->assertSame(0.0, $valores['valor_pagado']);
        $this->assertSame(20.0, $valores['saldo_pendiente']);
        $this->assertSame('Pendiente', $valores['estado']);
    }
}
