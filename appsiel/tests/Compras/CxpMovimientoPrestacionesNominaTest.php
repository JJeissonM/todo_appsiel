<?php

use App\CxP\CxpMovimiento;
use Illuminate\Support\Facades\DB;

class CxpMovimientoPrestacionesNominaTest extends TestCase
{
    public function test_excluye_los_detalles_de_prestaciones_de_nomina()
    {
        $query = DB::table('cxp_movimientos');

        CxpMovimiento::aplicarFiltroPrestacionesNomina($query, false);

        $sql = strtolower($query->toSql());
        $this->assertContains('`cxp_movimientos`.`detalle` not in (?, ?, ?, ?)', $sql);
        $this->assertContains('`cxp_movimientos`.`detalle` is null', $sql);
        $this->assertSame([
            'vacaciones',
            'prima_legal',
            'cesantias',
            'intereses_cesantias'
        ], $query->getBindings());
    }

    public function test_no_filtra_prestaciones_cuando_se_solicita_incluirlas()
    {
        $query = DB::table('cxp_movimientos');

        CxpMovimiento::aplicarFiltroPrestacionesNomina($query, true);

        $this->assertNotContains('detalle', strtolower($query->toSql()));
        $this->assertSame([], $query->getBindings());
    }
}
