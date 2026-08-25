<?php

use App\Core\Services\TurnoAssignmentResolver;
use App\Core\Services\TurnoContext;
use App\Core\Services\TurnoManager;
use App\Core\Services\TurnoModeResolver;
use App\Core\TurnoConfiguracion;
use App\Core\TurnoEvento;
use App\Core\TurnoOperativo;
use App\Inventarios\InvMovimiento;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\AperturaEncabezado;
use App\VentasPos\CierreEncabezado;
use App\VentasPos\FacturaPos;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TurnoOperativoIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown()
    {
        app(TurnoContext::class)->clear();
        app(TurnoModeResolver::class)->clearCache();
        parent::tearDown();
    }

    public function test_ciclo_transversal_turnos_cruce_medianoche_y_multiples_en_un_dia()
    {
        TurnoConfiguracion::create(array(
            'core_empresa_id' => 1,
            'modulo' => '*',
            'contexto_tipo' => '*',
            'contexto_id' => 0,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        app(TurnoModeResolver::class)->clearCache();

        $manager = app(TurnoManager::class);
        $firstOpening = $this->opening('2026-08-22', '2026-08-22 22:00:00', 900001);
        $firstTurn = $manager->openFromLegacy($firstOpening, 3);

        $this->assertSame('2026-08-22', $firstTurn->fecha_operativa);
        $this->assertTrue($firstTurn->estaAbierto());
        $this->assertSame($firstTurn->id, $firstOpening->fresh()->turno_operativo_id);

        $posInvoice = $this->posInvoice(910001);
        $this->assertSame($firstTurn->id, $posInvoice->turno_operativo_id);

        $standard = new VtasDocEncabezado(array('core_empresa_id' => 1));
        app(TurnoContext::class)->run($firstTurn, function () use ($standard) {
            app(TurnoAssignmentResolver::class)->assign($standard, 'ventas');
        });
        $this->assertSame($firstTurn->id, $standard->turno_operativo_id);

        $treasury = $this->treasuryMovement(910001);
        $this->assertSame($firstTurn->id, $treasury->turno_operativo_id);

        $inventory = new InvMovimiento($this->documentIdentity(910001));
        app(TurnoAssignmentResolver::class)->assign($inventory, 'inventarios');
        $this->assertSame($firstTurn->id, $inventory->turno_operativo_id);

        $firstClosing = $this->closing('2026-08-23', '2026-08-23 06:00:00', 900001);
        $manager->closeFromLegacy($firstClosing, 3, 125000);
        $firstTurn = $firstTurn->fresh();
        $this->assertSame(TurnoOperativo::ESTADO_CERRADO, $firstTurn->estado);
        $this->assertSame('2026-08-22', $firstTurn->fecha_operativa);
        $this->assertSame('2026-08-23 06:00:00', $firstTurn->cerrado_en->format('Y-m-d H:i:s'));

        $manager->reopen($firstTurn, 'Revision autorizada del cierre', 1);
        $this->assertTrue($firstTurn->fresh()->estaAbierto());
        $manager->closeFromLegacy($this->closing('2026-08-23', '2026-08-23 07:00:00', 900003), 3, 125000);
        $firstTurn = $firstTurn->fresh();

        $manager->startAudit($firstTurn, 1, 'Inicio de revision');
        $this->assertSame(TurnoOperativo::ESTADO_AUDITANDO, $firstTurn->estado);
        $manager->completeAudit($firstTurn, 1, 'Valores verificados');
        $this->assertSame(TurnoOperativo::ESTADO_AUDITADO, $firstTurn->estado);

        $manager->assignAdjustment($posInvoice, $firstTurn, 'Correccion auditada posterior al cierre', 1);
        $this->assertSame(1, TurnoEvento::where('turno_operativo_id', $firstTurn->id)->where('tipo', 'AJUSTE_POSTERIOR')->count());

        $cashMovements = TesoMovimiento::movimiento_por_tipo_motivo(
            'entrada', '1900-01-01', '1900-01-01', 1, null, 1, null, null, $firstTurn->id
        );
        $this->assertSame(321.0, (float)$cashMovements->sum('valor_movimiento'));

        $secondOpening = $this->opening('2026-08-22', '2026-08-22 14:00:00', 900002);
        $secondTurn = $manager->openFromLegacy($secondOpening, 3);
        $this->assertNotSame($firstTurn->id, $secondTurn->id);
        $this->assertSame($firstTurn->fecha_operativa, $secondTurn->fecha_operativa);
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 18:00:00', 900002), 3);
    }

    public function test_modo_tradicional_no_asigna_turno_ni_reconstruye_historicos()
    {
        app(TurnoModeResolver::class)->clearCache();
        $movement = new TesoMovimiento(array(
            'core_empresa_id' => 999999,
            'pdv_id' => 999999,
            'fecha' => '2020-01-01',
        ));

        app(TurnoAssignmentResolver::class)->assign($movement, 'tesoreria', 999999);
        $this->assertNull($movement->turno_operativo_id);
    }

    protected function opening($operationalDate, $createdAt, $consecutive)
    {
        $opening = new AperturaEncabezado(array(
            'core_tipo_transaccion_id' => 45,
            'core_tipo_doc_app_id' => 45,
            'consecutivo' => $consecutive,
            'fecha' => $operationalDate,
            'core_empresa_id' => 1,
            'cajero_id' => 3,
            'pdv_id' => 1,
            'efectivo_base' => 50000,
            'detalle' => 'Apertura de prueba de turnos',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
            'estado' => 'Activo',
        ));
        $opening->created_at = $createdAt;
        $opening->updated_at = $createdAt;
        $opening->save();
        return $opening;
    }

    protected function closing($date, $createdAt, $consecutive)
    {
        $closing = new CierreEncabezado(array(
            'core_tipo_transaccion_id' => 46,
            'core_tipo_doc_app_id' => 46,
            'consecutivo' => $consecutive,
            'fecha' => $date,
            'core_empresa_id' => 1,
            'cajero_id' => 3,
            'pdv_id' => 1,
            'detalle' => 'Cierre de prueba de turnos',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
            'estado' => 'Activo',
        ));
        $closing->created_at = $createdAt;
        $closing->updated_at = $createdAt;
        $closing->save();
        return $closing;
    }

    protected function posInvoice($consecutive)
    {
        $invoice = new FacturaPos($this->documentIdentity($consecutive) + array(
            'uniqid' => uniqid('turn-test-', true),
            'core_tercero_id' => 1,
            'remision_doc_encabezado_id' => 0,
            'ventas_doc_relacionado_id' => 0,
            'cliente_id' => 1,
            'vendedor_id' => 1,
            'pdv_id' => 1,
            'cajero_id' => 3,
            'forma_pago' => 'contado',
            'fecha_entrega' => '2026-08-22 22:30:00',
            'fecha_vencimiento' => '2026-08-22',
            'lineas_registros_medios_recaudos' => '[]',
            'descripcion' => 'Factura de prueba de turnos',
            'valor_total' => 10000,
            'efectivo_recibido' => 10000,
            'estado' => 'Activo',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
            'lote_acumulacion' => '',
        ));
        $invoice->efectivo_recibido = 10000;
        $invoice->save();
        return $invoice;
    }

    protected function documentIdentity($consecutive)
    {
        return array(
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 47,
            'core_tipo_doc_app_id' => 45,
            'consecutivo' => $consecutive,
            'fecha' => '2026-08-22',
        );
    }

    protected function treasuryMovement($consecutive)
    {
        return TesoMovimiento::create($this->documentIdentity($consecutive) + array(
            'core_tercero_id' => 1,
            'codigo_referencia_tercero' => '',
            'teso_medio_recaudo_id' => 1,
            'teso_motivo_id' => 1,
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'pdv_id' => 1,
            'valor_movimiento' => 321,
            'documento_soporte' => '',
            'descripcion' => 'Movimiento transversal de prueba',
            'estado' => 'Activo',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
        ));
    }
}
