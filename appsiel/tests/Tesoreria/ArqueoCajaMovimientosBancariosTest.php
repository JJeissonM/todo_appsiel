<?php

use App\Core\TurnoOperativo;
use App\VentasPos\Pdv;
use App\VentasPos\Services\ReportsServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class ArqueoCajaMovimientosBancariosTest extends TestCase
{
    use DatabaseTransactions;

    public function test_el_print_usa_el_turno_y_no_el_pdv_para_recaudos_bancarios()
    {
        $pdv = Pdv::whereNotNull('caja_default_id')->first();
        $this->assertNotNull($pdv);

        $turno = $this->crearTurno($pdv, '2099-09-11');
        $otroTurno = $this->crearTurno($pdv, '2099-09-11');

        $anticipoId = $this->crearMovimientoBancario($pdv, $turno->id, 8, null, 120000);
        $recaudoCxcId = $this->crearMovimientoBancario(
            $pdv,
            $turno->id,
            (int)config('tesoreria.recaudos_cxc_tipo_transaccion_id'),
            (int)$pdv->id,
            9000
        );
        $otroTurnoId = $this->crearMovimientoBancario($pdv, $otroTurno->id, 8, (int)$pdv->id, 7777);

        $result = (new ReportsServices())->get_movimentos_cuentas_bancarias(
            '2099-09-11',
            (int)$pdv->caja_default_id,
            'usuario-arqueo@appsiel.test',
            (int)$turno->id,
            (int)$pdv->core_empresa_id
        );
        $ids = $result->flatten(1)->pluck('id')->map(function ($id) {
            return (int)$id;
        })->all();

        $this->assertContains($anticipoId, $ids, 'Debe incluir el recaudo general aunque pdv_id sea NULL.');
        $this->assertContains($recaudoCxcId, $ids, 'Debe incluir el recaudo de cartera asociado al turno.');
        $this->assertNotContains($otroTurnoId, $ids, 'No debe mezclar movimientos de otro turno.');
    }

    public function test_arqueo_historico_sin_turno_incluye_recaudo_general_con_pdv_nulo()
    {
        $pdv = Pdv::whereNotNull('caja_default_id')->first();
        $this->assertNotNull($pdv);
        $movementId = $this->crearMovimientoBancario($pdv, null, 8, null, 33000, 'legacy-arqueo@appsiel.test');

        $result = (new ReportsServices())->get_movimentos_cuentas_bancarias(
            '2099-09-11',
            (int)$pdv->caja_default_id,
            'legacy-arqueo@appsiel.test',
            null,
            (int)$pdv->core_empresa_id
        );
        $ids = $result->flatten(1)->pluck('id')->map(function ($id) {
            return (int)$id;
        })->all();

        $this->assertContains($movementId, $ids);
    }

    protected function crearTurno($pdv, $date)
    {
        return TurnoOperativo::create(array(
            'core_empresa_id' => (int)$pdv->core_empresa_id,
            'contexto_tipo' => 'pdv',
            'contexto_id' => (int)$pdv->id,
            'pdv_id' => (int)$pdv->id,
            'teso_caja_id' => (int)$pdv->caja_default_id,
            'fecha_operativa' => $date,
            'abierto_en' => $date . ' 06:00:00',
            'cerrado_en' => $date . ' 14:00:00',
            'estado' => TurnoOperativo::ESTADO_CERRADO,
            'codigo' => 'TEST-ARQUEO-BANCO-' . uniqid(),
        ));
    }

    protected function crearMovimientoBancario($pdv, $turnId, $transactionType, $pdvId, $amount, $createdBy = 'usuario-arqueo@appsiel.test')
    {
        $template = (array)DB::table('teso_movimientos')->first();
        $this->assertNotEmpty($template);
        unset($template['id']);

        $excludedMotives = array_filter(array(
            (int)config('ventas_pos.motivo_tesoreria_propinas'),
            (int)config('ventas_pos.motivo_tesoreria_datafono'),
        ));
        $motive = DB::table('teso_motivos')->whereNotIn('id', $excludedMotives)->first();
        $bankAccount = DB::table('teso_cuentas_bancarias')
            ->where('core_empresa_id', (int)$pdv->core_empresa_id)
            ->first();
        $this->assertNotNull($motive);
        $this->assertNotNull($bankAccount);

        $template = array_merge($template, array(
            'fecha' => '2099-09-11',
            'core_empresa_id' => (int)$pdv->core_empresa_id,
            'core_tipo_transaccion_id' => (int)$transactionType,
            'turno_operativo_id' => is_null($turnId) ? null : (int)$turnId,
            'teso_motivo_id' => (int)$motive->id,
            'teso_caja_id' => 0,
            'teso_cuenta_bancaria_id' => (int)$bankAccount->id,
            'pdv_id' => is_null($pdvId) ? null : (int)$pdvId,
            'valor_movimiento' => (float)$amount,
            'descripcion' => 'Movimiento bancario de prueba de arqueo',
            'estado' => 'Activo',
            'creado_por' => $createdBy,
            'modificado_por' => $createdBy,
            'created_at' => '2099-09-11 10:00:00',
            'updated_at' => '2099-09-11 10:00:00',
        ));

        return (int)DB::table('teso_movimientos')->insertGetId($template);
    }
}
