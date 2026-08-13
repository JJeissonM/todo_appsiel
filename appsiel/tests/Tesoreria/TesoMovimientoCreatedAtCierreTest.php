<?php

use App\Tesoreria\TesoMovimiento;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TesoMovimientoCreatedAtCierreTest extends TestCase
{
    use DatabaseTransactions;

    public function test_asigna_created_at_del_ultimo_cierre_del_pdv_en_la_fecha_del_movimiento()
    {
        $pdv_id = 999999;

        $this->crearCierre($pdv_id, '2026-08-02', '2026-08-03 05:00:00', 1);
        $this->crearCierre($pdv_id, '2026-08-02', '2026-08-03 06:00:07', 2);
        $this->crearCierre($pdv_id, '2026-08-01', '2026-08-04 10:00:00', 3);

        $movimiento = new TesoMovimiento([
            'fecha' => '2026-08-02',
            'pdv_id' => $pdv_id
        ]);

        $movimiento->sincronizarCreatedAtConUltimoCierre();

        $this->assertSame('2026-08-03 06:00:07', $movimiento->created_at->format('Y-m-d H:i:s'));
    }

    public function test_conserva_created_at_sin_asignar_si_no_hay_cierre_para_fecha_y_pdv()
    {
        $this->crearCierre(999998, '2026-08-02', '2026-08-03 06:00:07', 4);

        $movimiento = new TesoMovimiento([
            'fecha' => '2026-08-02',
            'pdv_id' => 999999
        ]);

        $movimiento->sincronizarCreatedAtConUltimoCierre();

        $this->assertNull($movimiento->created_at);
    }

    protected function crearCierre($pdv_id, $fecha, $created_at, $consecutivo)
    {
        DB::table('vtas_pos_cierre_encabezados')->insert([
            'core_tipo_transaccion_id' => 999999,
            'core_tipo_doc_app_id' => 999999,
            'consecutivo' => $consecutivo,
            'fecha' => $fecha,
            'core_empresa_id' => 999999,
            'cajero_id' => 999999,
            'pdv_id' => $pdv_id,
            'detalle' => 'Cierre de prueba',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
            'estado' => 'Activo',
            'created_at' => $created_at,
            'updated_at' => $created_at
        ]);
    }
}
