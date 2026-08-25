<?php

use App\Core\Services\TurnoAssignmentResolver;
use App\Core\Services\TurnoConfigurationService;
use App\Core\Services\TurnoContext;
use App\Core\Services\TurnoEnvelope;
use App\Core\Services\TurnoManager;
use App\Core\Services\TurnoModeResolver;
use App\Core\TurnoConfiguracion;
use App\Core\TurnoEvento;
use App\Core\TurnoOperativo;
use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Exceptions\TurnoRequiredException;
use App\Core\Exceptions\TurnoStateException;
use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelOrderLine;
use App\Inventarios\InvMovimiento;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\ArqueoCaja;
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

        $adjustmentCreatedAt = (string)$posInvoice->created_at;
        $manager->assignAdjustment($posInvoice, $firstTurn, 'Correccion auditada posterior al cierre', 1);
        $adjustmentEvent = TurnoEvento::where('turno_operativo_id', $firstTurn->id)->where('tipo', 'AJUSTE_POSTERIOR')->first();
        $this->assertNotNull($adjustmentEvent);
        $this->assertSame(1, (int)$adjustmentEvent->usuario_id);
        $this->assertSame('Correccion auditada posterior al cierre', $adjustmentEvent->motivo);
        $this->assertSame($adjustmentCreatedAt, (string)$posInvoice->fresh()->created_at);

        $eventTypes = TurnoEvento::where('turno_operativo_id', $firstTurn->id)->pluck('tipo')->toArray();
        foreach (array('APERTURA', 'CIERRE', 'REAPERTURA', 'INICIO_AUDITORIA', 'FIN_AUDITORIA', 'AJUSTE_POSTERIOR') as $eventType) {
            $this->assertContains($eventType, $eventTypes);
        }
        $this->assertSame(0, TurnoEvento::where('turno_operativo_id', $firstTurn->id)->whereNull('usuario_id')->count());

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

    public function test_apertura_y_cierre_tradicional_no_crean_turno_explicito()
    {
        $manager = app(TurnoManager::class);
        $opening = $this->opening('2026-08-22', '2026-08-22 08:00:00', 919999);
        $closing = $this->closing('2026-08-22', '2026-08-22 18:00:00', 919999);

        $this->assertNull($manager->openFromLegacy($opening, 3));
        $this->assertNull($manager->closeFromLegacy($closing, 3));
        $this->assertNull($opening->fresh()->turno_operativo_id);
        $this->assertNull($closing->fresh()->turno_operativo_id);
    }

    public function test_modo_turnos_rechaza_operacion_sin_apertura()
    {
        $this->configure('ventas_pos', 'pdv', 1, TurnoConfiguracion::MODO_TURNOS);
        $this->setExpectedException(TurnoRequiredException::class, 'Debe realizar la apertura');
        $this->newPosInvoice(920001)->save();
    }

    public function test_rechaza_turno_de_otra_empresa()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $turno = app(TurnoManager::class)->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920002), 3);
        $invoice = $this->newPosInvoice(920002);
        $invoice->core_empresa_id = 999999;
        $invoice->turno_operativo_id = $turno->id;

        $this->setExpectedException(TurnoIntegrityException::class, 'otra empresa');
        $invoice->save();
    }

    public function test_rechaza_turno_de_otro_contexto()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $turno = app(TurnoManager::class)->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920003), 3);
        $invoice = $this->newPosInvoice(920003);
        $invoice->pdv_id = 999999;
        $invoice->turno_operativo_id = $turno->id;

        $this->setExpectedException(TurnoIntegrityException::class, 'otro contexto');
        $invoice->save();
    }

    public function test_rechaza_turno_cerrado_para_operacion_normal_y_cambio_directo_de_estado()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $turno = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920004), 3);
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 12:00:00', 920004), 3);

        try {
            $invoice = $this->newPosInvoice(920004);
            $invoice->turno_operativo_id = $turno->id;
            $invoice->save();
            $this->fail('Debió rechazarse el turno cerrado.');
        } catch (TurnoStateException $e) {
            $this->assertContains('turno cerrado', $e->getMessage());
        }

        $turno = $turno->fresh();
        $turno->estado = TurnoOperativo::ESTADO_ABIERTO;
        $this->setExpectedException(TurnoStateException::class, 'TurnoManager');
        $turno->save();
    }

    public function test_arqueo_puede_referenciar_explicita_y_unicamente_un_turno_cerrado()
    {
        $this->configure('*', 'pdv', 1, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $turno = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920040), 3);
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 12:00:00', 920040), 3);

        $arqueo = new ArqueoCaja(array(
            'core_empresa_id' => 1,
            'pdv_id' => 1,
            'turno_operativo_id' => $turno->id,
        ));
        app(TurnoAssignmentResolver::class)->assign($arqueo, 'tesoreria');

        $this->assertSame((int)$turno->id, (int)$arqueo->turno_operativo_id);
    }

    public function test_no_permite_dos_turnos_abiertos_para_el_mismo_contexto()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920005), 3);

        $this->setExpectedException(UnexpectedValueException::class, 'Ya existe');
        $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 09:00:00', 920006), 3);
    }

    public function test_turno_explicito_no_puede_contradecir_el_contexto_propagado()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $anterior = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920050), 3);
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 10:00:00', 920050), 3);
        $actual = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 11:00:00', 920051), 3);

        $invoice = $this->newPosInvoice(920052);
        $invoice->turno_operativo_id = $anterior->id;

        $this->setExpectedException(TurnoIntegrityException::class, 'contradice');
        app(TurnoContext::class)->run($actual, function () use ($invoice) {
            $invoice->save();
        });
    }

    public function test_sobre_diferido_conserva_turno_cerrado_en_reintentos()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $turno = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920007), 3);
        $origin = $this->posInvoice(920007);
        $serialized = TurnoEnvelope::fromOrigin($origin)->toArray();
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 12:00:00', 920007), 3);

        $ids = array();
        foreach (array(920008, 920009) as $consecutive) {
            $ids[] = TurnoEnvelope::fromArray($serialized)->run(function () use ($consecutive) {
                return $this->treasuryMovement($consecutive)->id;
            });
        }

        $movements = TesoMovimiento::whereIn('id', $ids)->get();
        $this->assertCount(2, $movements);
        foreach ($movements as $movement) {
            $this->assertSame((int)$turno->id, (int)$movement->turno_operativo_id);
            $this->assertNotSame('2026-08-22 12:00:00', $movement->created_at->format('Y-m-d H:i:s'));
        }
    }

    public function test_venta_estandar_y_documento_electronico_derivado_conservan_turno_cerrado()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $turno = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920013), 3);
        $standard = $this->standardInvoice(920013);
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 12:00:00', 920013), 3);

        $electronic = $this->newStandardInvoice(920014);
        $electronic->core_tipo_transaccion_id = 52;
        $electronic->ventas_doc_relacionado_id = $standard->id;
        $electronic->turno_operativo_id = $turno->id;
        $electronic->save();

        $this->assertSame((int)$turno->id, (int)$standard->turno_operativo_id);
        $this->assertSame((int)$turno->id, (int)$electronic->turno_operativo_id);
    }

    public function test_cargos_de_una_estadia_pueden_pertenecer_a_turnos_distintos()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $first = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920010), 3);
        $order = HotelOrderHeader::create(array(
            'empresa_id' => 1, 'stay_id' => 999999, 'cliente_id' => 1, 'pdv_id' => 1,
            'document_number' => 'TURN-TEST', 'order_date' => '2026-08-22 08:10:00',
            'status' => HotelOrderHeader::STATUS_ABIERTO, 'created_by' => 3,
        ));
        $firstLine = $this->hotelLine($order, 'Cargo turno uno');
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 12:00:00', 920010), 3);

        $second = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 13:00:00', 920011), 3);
        $secondLine = $this->hotelLine($order, 'Cargo turno dos');

        $this->assertSame((int)$first->id, (int)$firstLine->turno_operativo_id);
        $this->assertSame((int)$second->id, (int)$secondLine->turno_operativo_id);
        $this->assertNotSame($firstLine->turno_operativo_id, $secondLine->turno_operativo_id);
    }

    public function test_configuracion_advierte_modos_mixtos_e_impide_modulo_no_integrado()
    {
        $service = app(TurnoConfigurationService::class);
        $analysis = $service->analyzeCandidate(array(
            'core_empresa_id' => 1, 'modulo' => 'ventas_pos', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
        $this->assertNotEmpty($analysis['warnings']);

        $this->setExpectedException(TurnoIntegrityException::class, 'todavía no persiste turnos');
        $service->configure(array(
            'core_empresa_id' => 1, 'modulo' => 'compras', 'contexto_tipo' => 'pdv',
            'contexto_id' => 1, 'modo' => TurnoConfiguracion::MODO_TURNOS,
        ));
    }

    public function test_nucleo_soporta_contexto_distinto_de_pdv()
    {
        $this->configure('tesoreria', 'caja', 1, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $turno = $manager->openContext(1, 'tesoreria', 'caja', 1, '2026-08-22', 1, 5000, '2026-08-22 07:00:00');

        $this->assertSame('caja', $turno->contexto_tipo);
        $this->assertSame(1, (int)$turno->contexto_id);
        $this->assertSame($turno->id, $manager->currentForContext(1, 'caja', 1)->id);

        $manager->close($turno, 1, 5500, 'Cierre de caja', '2026-08-22 15:00:00');
        $this->assertSame(TurnoOperativo::ESTADO_CERRADO, $turno->estado);
        $this->assertNull($manager->currentForContext(1, 'caja', 1));
    }

    public function test_ajuste_y_reapertura_exigen_motivo_y_usuario()
    {
        $this->configure('*', '*', 0, TurnoConfiguracion::MODO_TURNOS);
        $manager = app(TurnoManager::class);
        $turno = $manager->openFromLegacy($this->opening('2026-08-22', '2026-08-22 08:00:00', 920012), 3);
        $origin = $this->posInvoice(920012);
        $manager->closeFromLegacy($this->closing('2026-08-22', '2026-08-22 12:00:00', 920012), 3);

        try {
            $manager->assignAdjustment($origin, $turno, 'Motivo válido', null);
            $this->fail('Debió exigir usuario para el ajuste.');
        } catch (TurnoIntegrityException $e) {
            $this->assertContains('usuario responsable', $e->getMessage());
        }

        $this->setExpectedException(InvalidArgumentException::class, 'motivo');
        $manager->reopen($turno, '', 1);
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
        $invoice = $this->newPosInvoice($consecutive);
        $invoice->save();
        return $invoice;
    }

    protected function newPosInvoice($consecutive)
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

    protected function standardInvoice($consecutive)
    {
        $invoice = $this->newStandardInvoice($consecutive);
        $invoice->save();
        return $invoice;
    }

    protected function newStandardInvoice($consecutive)
    {
        $invoice = new VtasDocEncabezado(array(
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 23,
            'core_tipo_doc_app_id' => 18,
            'consecutivo' => $consecutive,
            'fecha' => '2026-08-22',
            'core_tercero_id' => 1,
            'remision_doc_encabezado_id' => '0',
            'ventas_doc_relacionado_id' => 0,
            'cliente_id' => 1,
            'contacto_cliente_id' => 0,
            'vendedor_id' => 1,
            'inv_bodega_id' => 1,
            'forma_pago' => 'contado',
            'fecha_entrega' => '2026-08-22',
            'hora_entrega' => '08:00:00',
            'plazo_entrega_id' => 0,
            'fecha_vencimiento' => '2026-08-22',
            'orden_compras' => '',
            'descripcion' => 'Venta estándar de prueba de turno',
            'valor_total' => 100,
            'estado' => 'Activo',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
        ));
        $invoice->efectivo_recibido = 100;
        return $invoice;
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

    protected function hotelLine(HotelOrderHeader $order, $description)
    {
        return HotelOrderLine::create(array(
            'empresa_id' => 1,
            'hotel_order_id' => $order->id,
            'producto_id' => 1,
            'description' => $description,
            'quantity' => 1,
            'unit_price' => 100,
            'discount' => 0,
            'tax_value' => 0,
            'source_type' => HotelOrderLine::SOURCE_MANUAL,
        ));
    }

    protected function configure($module, $contextType, $contextId, $mode)
    {
        TurnoConfiguracion::create(array(
            'core_empresa_id' => 1,
            'modulo' => $module,
            'contexto_tipo' => $contextType,
            'contexto_id' => $contextId,
            'modo' => $mode,
        ));
        app(TurnoModeResolver::class)->clearCache();
    }
}
