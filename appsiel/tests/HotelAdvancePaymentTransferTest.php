<?php

use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;
use App\CxC\CxcAbono;
use App\CxC\CxcDocEncabezado;
use App\CxC\CxcMovimiento;
use App\Hotel\Services\HotelAdvancePaymentService;
use App\Hotel\Services\HotelService;
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\CxC\DocCruceController;
use App\Tesoreria\TesoMotivo;
use App\VentasPos\Services\TreasuryService;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\Services\DocumentHeaderService as SalesDocumentHeaderService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;

/** Sin bootstrap de la aplicación ni .env: solo SQLite en memoria. */
class HotelAdvancePaymentTransferTest extends PHPUnit_Framework_TestCase
{
    private $oldContainer;
    private $oldFacadeApplication;
    private $oldResolver;
    private $oldDispatcher;
    private $advance;
    private $invoice;

    protected function setUp()
    {
        parent::setUp();
        $this->oldContainer = Container::getInstance() ?: new Container();
        $this->oldFacadeApplication = Facade::getFacadeApplication();
        $this->oldResolver = Model::getConnectionResolver();
        $this->oldDispatcher = Model::getEventDispatcher();
        $container = new Container();
        $container->instance('config', new Repository());
        $db = new Capsule($container);
        $db->addConnection(array('driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''));
        $db->setEventDispatcher(new Dispatcher($container));
        $db->bootEloquent();
        $container->instance('db', $db->getDatabaseManager());
        $container->bind('db.schema', function () use ($db) { return $db->getConnection()->getSchemaBuilder(); });
        $container['config']->set(array(
            'contabilidad' => array('transaction_type_id_default' => 9, 'document_type_id_default' => 15),
            'ventas' => array('cruces_cxc_tipo_doc_app_id' => 16, 'cruces_cxc_tipo_transaccion_id' => 39, 'cruces_cxc_modelo_id' => 215),
            'tesoreria' => array('recaudos_cxc_motivo_id' => 4, 'tipo_operacion_recaudos_cxc' => 'recaudo-cartera'),
        ));
        $auth = Mockery::mock();
        $auth->shouldReceive('user')->andReturn((object)array('email' => 'test@example.com', 'empresa_id' => 1));
        $container->instance('auth', $auth);
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();
        Model::clearBootedModels();

        foreach (array(new CxcMovimiento(), new CxcAbono(), new CxcDocEncabezado(),
            new ContabDocEncabezado(), new ContabDocRegistro(), new ContabMovimiento(), new TesoMotivo(), new VtasDocEncabezado()) as $model) {
            Schema::create($model->getTable(), function ($table) use ($model) {
                $table->increments('id');
                foreach ($model->getFillable() as $column) {
                    if (preg_match('/^(valor_|saldo_|abono$)/', $column)) {
                        $table->double($column)->nullable();
                    } else {
                        $table->string($column)->nullable();
                    }
                }
                $table->timestamps();
            });
        }
        Schema::create('sys_modelos', function ($table) {
            $table->integer('id');
            $table->string('name_space');
        });
        DB::table('sys_modelos')->insert(array(
            array('id' => 47, 'name_space' => ContabDocEncabezado::class),
            array('id' => 215, 'name_space' => CxcDocEncabezado::class),
        ));
        Schema::create('core_tipos_docs_apps', function ($table) { $table->integer('id'); $table->string('prefijo')->nullable(); });
        DB::table('core_tipos_docs_apps')->insert(array(array('id' => 15, 'prefijo' => 'NC'), array('id' => 16, 'prefijo' => 'CR')));
        Schema::create('core_consecutivos_documentos', function ($table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_documento_app_id');
            $table->integer('consecutivo_actual');
            $table->timestamps();
        });
        $this->advance = $this->movement(8, 3, 672, 2173, -120000, 219);
        DB::table('teso_motivos')->insert(array('id' => 1, 'descripcion' => 'Recaudo clientes', 'movimiento' => 'entrada', 'teso_tipo_motivo' => 'recaudo-cartera'));
        $this->movement(47, 18, 42, 2176, 120000, 27);
        $this->invoice = new HotelTransferDisplayTestInvoice(array('core_empresa_id' => 1, 'core_tipo_transaccion_id' => 47,
            'core_tipo_doc_app_id' => 18, 'consecutivo' => 42, 'core_tercero_id' => 2176,
            'fecha' => '2026-10-11', 'valor_total' => 120000, 'creado_por' => 'test@example.com'));
    }

    protected function tearDown()
    {
        DB::purge();
        $this->oldResolver ? Model::setConnectionResolver($this->oldResolver) : Model::unsetConnectionResolver();
        $this->oldDispatcher ? Model::setEventDispatcher($this->oldDispatcher) : Model::unsetEventDispatcher();
        Model::clearBootedModels();
        Container::setInstance($this->oldContainer);
        Facade::setFacadeApplication($this->oldFacadeApplication);
        Facade::clearResolvedInstances();
        Mockery::close();
        parent::tearDown();
    }

    private function movement($transaction, $type, $number, $thirdParty, $value, $account)
    {
        $identity = array('core_empresa_id' => 1, 'core_tipo_transaccion_id' => $transaction,
            'core_tipo_doc_app_id' => $type, 'consecutivo' => $number, 'core_tercero_id' => $thirdParty);
        $movement = CxcMovimiento::create($identity + array('valor_documento' => $value, 'saldo_pendiente' => $value,
            'valor_pagado' => 0, 'estado' => 'Pendiente', 'fecha' => '2026-10-11'));
        ContabMovimiento::create($identity + array('contab_cuenta_id' => $account,
            'valor_debito' => $value > 0 ? $value : 0, 'valor_credito' => $value < 0 ? $value : 0));
        return $movement;
    }

    private function advances($amount = 120000)
    {
        return json_encode(array('cxc_movimiento_id' => $this->advance->id, 'valor_aplicar' => -$amount));
    }

    private function apply($amount = 120000)
    {
        return (new HotelAdvancePaymentService())->apply($this->invoice, $this->advances($amount), 2173, 'HOT-542');
    }

    public function test_nota_credito_electronica_permite_turno_distinto_a_factura_y_pos()
    {
        Schema::create('vtas_pos_doc_encabezados', function ($table) {
            $table->integer('id'); $table->integer('core_empresa_id'); $table->integer('turno_operativo_id');
        });
        Schema::create('core_turnos_operativos', function ($table) {
            $table->integer('id'); $table->integer('core_empresa_id'); $table->string('estado')->nullable(); $table->timestamp('cerrado_en')->nullable();
        });
        DB::table('core_turnos_operativos')->insert(array(array('id' => 10, 'core_empresa_id' => 1, 'estado' => 'CERRADO'), array('id' => 20, 'core_empresa_id' => 1, 'estado' => 'ABIERTO')));
        DB::table('vtas_doc_encabezados')->insert(array('id' => 5, 'core_empresa_id' => 1, 'turno_operativo_id' => 10));
        DB::table('vtas_pos_doc_encabezados')->insert(array('id' => 5, 'core_empresa_id' => 1, 'turno_operativo_id' => 20));
        $note = new App\FacturacionElectronica\NotaCredito(array('core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 53, 'ventas_doc_relacionado_id' => 5, 'turno_operativo_id' => 20));
        $this->assertEquals(20, $note->turno_operativo_id);
        $class = new ReflectionClass(App\Core\Services\TurnoAssignmentResolver::class);
        $method = $class->getMethod('fromPersistedOrigin');
        $method->setAccessible(true);
        $origin = $method->invoke($class->newInstanceWithoutConstructor(), $note);
        $this->assertNull($origin);
        Container::getInstance()->instance('request', new Illuminate\Http\Request());
        $resolver = new App\Core\Services\TurnoAssignmentResolver(new App\Core\Services\TurnoContext(),
            Mockery::mock(App\Core\Services\TurnoManager::class), new App\Core\Services\TurnoModeResolver());
        $this->assertEquals(20, $resolver->assign($note, 'facturacion_electronica'));
        foreach (array(App\Ventas\NotaCredito::class => 38, App\Ventas\NotaCreditoDirecta::class => 41) as $noteClass => $transaction) {
            $salesNote = new $noteClass(array('core_empresa_id' => 1, 'core_tipo_transaccion_id' => $transaction,
                'ventas_doc_relacionado_id' => 5, 'turno_operativo_id' => 20));
            $this->assertEquals('ventas', $salesNote->getTurnoModuleName());
            $this->assertEquals(20, $resolver->assign($salesNote, 'ventas'));
        }
        Schema::create('inv_doc_encabezados', function ($table) {
            $table->integer('id'); $table->integer('core_empresa_id'); $table->integer('turno_operativo_id');
        });
        DB::table('inv_doc_encabezados')->insert(array('id' => 6, 'core_empresa_id' => 1, 'turno_operativo_id' => 10));
        $context = new App\Core\Services\TurnoContext();
        $derivedResolver = new App\Core\Services\TurnoAssignmentResolver($context,
            Mockery::mock(App\Core\Services\TurnoManager::class), new App\Core\Services\TurnoModeResolver());
        $devolution = new App\Inventarios\InvDocEncabezado(array('core_empresa_id' => 1, 'turno_operativo_id' => 20));
        $devolution->inv_doc_encabezado_id = 6;
        $context->runFromOrigin(App\Core\TurnoOperativo::find(20), get_class($note), 7, function () use ($derivedResolver, $devolution) {
            $this->assertEquals(20, $derivedResolver->assign($devolution, 'inventarios'));
        });
        $this->assertNull($context->current());
    }

    public function test_formulario_nota_credito_permite_seleccionar_turno_y_motivo()
    {
        $url = Mockery::mock();
        $url->shouldReceive('to')->andReturn('http://localhost/turnos/operativos/validar-seleccion');
        Container::getInstance()->instance('url', $url);
        Container::getInstance()->instance(Illuminate\Contracts\Routing\UrlGenerator::class, $url);
        $service = new ElectronicNoteOriginFormTestService(new App\Core\Services\TurnoModeResolver());
        config(array('turnos.simple_company_mode' => true, 'turnos.manual_assignment_models' =>
            array(App\FacturacionElectronica\NotaCredito::class => 'facturacion_electronica')));
        $note = new App\FacturacionElectronica\NotaCredito(array('core_empresa_id' => 1));
        $fields = $service->decorate((object)array('name_space' => App\FacturacionElectronica\NotaCredito::class), $note, 'create', array());
        $this->assertEquals('turno_operativo_id', $fields[0]['name']);
        $this->assertEquals('input_lista_sugerencias', $fields[0]['tipo']);
        $this->assertArrayNotHasKey('disabled', $fields[0]['atributos']);
        $this->assertEquals('turno_ajuste_motivo', $fields[1]['name']);
    }

    public function test_cruce_directo_del_huesped_a_la_empresa_sin_nota_contable()
    {
        $cross = (new HotelAdvancePaymentService())->applyDirect($this->invoice, $this->advances(), 2173, 'HOT-542');
        $this->assertSame(0, ContabDocEncabezado::count());
        $this->assertSame(1, CxcDocEncabezado::count());
        $this->assertSame(1, CxcAbono::count());
        $this->assertEquals(2173, CxcAbono::first()->core_tercero_id);
        $this->assertEquals(2176, $cross->core_tercero_id);
        $this->assertEquals(0, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(0, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
        $this->assertEquals(2173, ContabMovimiento::where('core_tipo_transaccion_id', 39)->where('valor_debito', '>', 0)->value('core_tercero_id'));
        $this->assertEquals(2176, ContabMovimiento::where('core_tipo_transaccion_id', 39)->where('valor_credito', '<', 0)->value('core_tercero_id'));
    }

    public function test_reversion_del_cruce_directo_restaura_los_dos_terceros()
    {
        $container = Container::getInstance();
        $container->instance('request', new Illuminate\Http\Request());
        $response = Mockery::mock();
        $response->shouldReceive('with')->andReturnSelf();
        $redirector = Mockery::mock();
        $redirector->shouldReceive('to')->andReturn($response);
        $container->instance('redirect', $redirector);
        Schema::create('teso_recaudos_libretas', function ($table) {
            $table->integer('core_tipo_transaccion_id'); $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo'); $table->double('valor_recaudo');
        });
        $cross = (new HotelAdvancePaymentService())->applyDirect($this->invoice, $this->advances(), 2173, 'HOT-542');
        (new DocCruceController())->anular_doc_cruce($cross->id);
        $this->assertEquals(-120000, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(120000, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
        $this->assertSame(0, CxcAbono::count());
        $this->assertEquals('Anulado', $cross->fresh()->estado);
    }

    public function test_cruce_directo_fallido_revierte_saldos_y_consecutivo()
    {
        ContabMovimiento::where('core_tipo_transaccion_id', 47)->delete();
        try {
            (new HotelAdvancePaymentService())->applyDirect($this->invoice, $this->advances(), 2173, 'HOT-542');
            $this->fail('Debe exigir la cuenta de cartera.');
        } catch (RuntimeException $error) {
            $this->assertContains('cuenta contable', $error->getMessage());
        }
        $this->assertSame(0, CxcDocEncabezado::count());
        $this->assertSame(0, CxcAbono::count());
        $this->assertEquals(-120000, $this->advance->fresh()->saldo_pendiente);
        $this->assertSame(0, DB::table('core_consecutivos_documentos')->count());
    }

    public function test_cruce_directo_parcial_conserva_el_saldo_restante()
    {
        (new HotelAdvancePaymentService())->applyDirect($this->invoice, $this->advances(70000), 2173, 'HOT-542');
        $this->assertEquals(-50000, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(50000, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
        $this->assertSame(0, ContabDocEncabezado::count());
        $this->assertSame(1, CxcDocEncabezado::count());
    }

    public function test_cliente_huesped_y_destinatario_electronico_se_conservan_separados()
    {
        Schema::create('vtas_clientes', function ($table) { $table->integer('id'); $table->integer('core_tercero_id'); });
        Schema::create('core_terceros', function ($table) { $table->integer('id'); $table->string('tipo'); });
        Schema::create('hotel_order_headers', function ($table) {
            $table->integer('id'); $table->integer('empresa_id'); $table->integer('cliente_id'); $table->integer('pos_doc_id');
        });
        DB::table('vtas_clientes')->insert(array(array('id' => 1, 'core_tercero_id' => 2173), array('id' => 2, 'core_tercero_id' => 2176)));
        DB::table('core_terceros')->insert(array(array('id' => 2173, 'tipo' => 'Persona natural'), array('id' => 2176, 'tipo' => 'Persona jurídica')));
        DB::table('hotel_order_headers')->insert(array('id' => 1, 'empresa_id' => 1, 'cliente_id' => 1, 'pos_doc_id' => 42));
        $pos = new App\VentasPos\FacturaPos(array('core_empresa_id' => 1, 'cliente_id' => 1, 'core_tercero_id' => 2176));
        $pos->id = 42;
        $recipient = (new App\VentasPos\Services\InvoiceCustomerService())->validateInvoice($pos);
        $this->assertEquals(2, $recipient->id);
        $this->assertEquals(1, $pos->cliente->id);
        $fe = new VtasDocEncabezado(array('cliente_id' => 1, 'core_tercero_id' => 2176));
        $this->assertEquals(2, (new App\FacturacionElectronica\Services\DocumentHeaderService())->get_cliente($fe)->id);
        $this->assertEquals(1, $fe->cliente_id);
        DB::table('hotel_order_headers')->delete();
        $this->setExpectedException('InvalidArgumentException', 'no corresponde al cliente asociado');
        (new App\VentasPos\Services\InvoiceCustomerService())->validateInvoice($pos);
    }

    public function test_vista_cruce_encuentra_nota_con_encabezado_de_otro_tercero()
    {
        $result = $this->apply();
        $method = new ReflectionMethod(DocCruceController::class, 'buscar_documento_cruzado');
        $method->setAccessible(true);
        $transaction = (object)array('id' => 9, 'modelo_encabezados_documentos' => ContabDocEncabezado::class);
        $identity = array('core_tipo_transaccion_id' => 9, 'core_tipo_doc_app_id' => 15,
            'consecutivo' => $result['note']->consecutivo);
        $note = $method->invoke(new DocCruceController(), $transaction, $identity, 1, 2176);
        $this->assertEquals($result['note']->id, $note->id);
        $this->assertEquals(2173, $note->core_tercero_id);
        $this->assertNull($method->invoke(new DocCruceController(), $transaction, $identity, 2, 2176));
    }

    public function test_abonos_de_factura_muestran_recaudo_original_del_traslado()
    {
        $this->apply(50000);
        $payments = CxcAbono::where('doc_cxc_transacc_id', 47)->get();
        $display = (new App\Ventas\Services\InvoicePaymentDisplayService())->resolve($payments);
        $this->assertCount(1, $display);
        $this->assertEquals(8, $display[0]->core_tipo_transaccion_id);
        $this->assertEquals(672, $display[0]->consecutivo);
        $this->assertEquals(50000, $display[0]->abono);
        $this->assertEquals(9, $payments[0]->core_tipo_transaccion_id);
        $this->assertSame(2, CxcAbono::count());
    }

    public function test_abonos_de_varias_cuentas_no_duplican_el_total_del_traslado()
    {
        $second = $this->movement(8, 3, 673, 2173, -40000, 220);
        (new HotelAdvancePaymentService())->apply($this->invoice, $this->advances(80000) . ',' .
            json_encode(array('cxc_movimiento_id' => $second->id, 'valor_aplicar' => -40000)), 2173, 'HOT-542');
        $display = (new App\Ventas\Services\InvoicePaymentDisplayService())->resolve(CxcAbono::where('doc_cxc_transacc_id', 47)->get());
        $this->assertEquals(120000, $display->sum('abono'));
        $this->assertEquals(array(672, 673), $display->pluck('consecutivo')->unique()->values()->all());
    }

    public function test_trazabilidad_incompleta_conserva_la_nota_y_su_importe()
    {
        $result = $this->apply();
        CxcDocEncabezado::where('id', $result['source_cross']->id)->update(array('estado' => 'Anulado'));
        $display = (new App\Ventas\Services\InvoicePaymentDisplayService())->resolve(CxcAbono::where('doc_cxc_transacc_id', 47)->get());
        $this->assertEquals(9, $display[0]->core_tipo_transaccion_id);
        $this->assertEquals(120000, $display->sum('abono'));
    }

    public function test_crea_nota_con_terceros_correctos_y_dos_cruces_sin_mover_dinero()
    {
        $result = $this->apply();
        $this->assertSame(1, ContabDocEncabezado::count());
        $this->assertSame(2, CxcDocEncabezado::count());
        $this->assertSame(2, CxcAbono::count());
        $lines = $result['note']->lineas_registros;
        $this->assertEquals(array('crear_cxc', 'crear_anticipo_cxc'), $lines->pluck('tipo_transaccion')->all());
        $this->assertEquals(array(2173, 2176), $lines->pluck('core_tercero_id')->all());
        $this->assertEquals(array(219, 219), $lines->pluck('contab_cuenta_id')->all());
        $this->assertEquals(120000, $lines->sum('valor_debito'));
        $this->assertEquals(120000, $lines->sum('valor_credito'));
        $this->assertEquals(2173, $result['source_cross']->core_tercero_id);
        $this->assertEquals(2176, $result['invoice_cross']->core_tercero_id);
        $this->assertEquals(0, CxcMovimiento::sum('saldo_pendiente'));
        $this->assertSame(4, CxcMovimiento::where('estado', 'Pagado')->count());
        $this->assertEquals(0, ContabMovimiento::where('core_tipo_transaccion_id', 9)->sum('valor_saldo'));
        $this->assertEquals(array(2173, 2176), ContabMovimiento::where('core_tipo_transaccion_id', 9)
            ->orderBy('id')->pluck('core_tercero_id')->all());
    }

    public function test_traslada_solo_el_valor_aplicado_y_deja_saldos_parciales()
    {
        $result = $this->apply(70000);
        $this->assertEquals(70000, $result['note']->valor_total);
        $this->assertEquals(-50000, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(50000, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
        $this->assertSame(2, CxcMovimiento::where('core_tipo_transaccion_id', 9)->where('estado', 'Pagado')->count());
    }

    public function test_mismo_tercero_conserva_un_solo_cruce_sin_nota()
    {
        $ownAdvance = $this->movement(8, 3, 673, 2176, -120000, 219);
        $result = (new HotelAdvancePaymentService())->apply($this->invoice,
            json_encode(array('cxc_movimiento_id' => $ownAdvance->id, 'valor_aplicar' => -120000)), 2173, 'HOT-542');
        $this->assertNull($result['note']);
        $this->assertSame(1, CxcDocEncabezado::count());
        $this->assertEquals(-120000, $this->advance->fresh()->saldo_pendiente);
    }

    public function test_combina_anticipos_del_huesped_y_de_la_empresa_sin_trasladar_los_propios()
    {
        $ownAdvance = $this->movement(8, 3, 673, 2176, -50000, 220);
        $objects = $this->advances(70000) . ',' . json_encode(array('cxc_movimiento_id' => $ownAdvance->id, 'valor_aplicar' => -50000));
        $result = (new HotelAdvancePaymentService())->apply($this->invoice, $objects, 2173, 'HOT-542');
        $this->assertEquals(70000, $result['note']->valor_total);
        $this->assertSame(2, $result['note']->lineas_registros->count());
        $this->assertSame(2, CxcDocEncabezado::count());
        $this->assertEquals(0, $ownAdvance->fresh()->saldo_pendiente);
        $this->assertEquals(0, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
    }

    public function test_varios_anticipos_y_cuentas_se_trasladan_en_una_nota_y_dos_cruces()
    {
        $second = $this->movement(8, 3, 673, 2173, -40000, 220);
        $objects = $this->advances(80000) . ',' . json_encode(array('cxc_movimiento_id' => $second->id, 'valor_aplicar' => -40000));
        $result = (new HotelAdvancePaymentService())->apply($this->invoice, $objects, 2173, 'HOT-542');
        $this->assertSame(4, $result['note']->lineas_registros->count());
        $this->assertEquals(array(219, 219, 220, 220), $result['note']->lineas_registros->pluck('contab_cuenta_id')->all());
        $this->assertSame(2, CxcDocEncabezado::count());
        $this->assertEquals(-40000, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(0, $second->fresh()->saldo_pendiente);
        $this->assertEquals(0, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
    }

    public function test_fallo_del_segundo_cruce_revierte_nota_primer_cruce_saldos_y_consecutivos()
    {
        // Sin cuenta de cartera: el primer cruce termina, el segundo falla al contabilizar.
        ContabMovimiento::where('core_tipo_transaccion_id', 47)->delete();
        try {
            $this->apply();
            $this->fail('Debe fallar el segundo cruce.');
        } catch (RuntimeException $error) {
            $this->assertContains('cuenta contable', $error->getMessage());
        }
        $this->assertSame(0, ContabDocEncabezado::count());
        $this->assertSame(0, ContabDocRegistro::count());
        $this->assertSame(0, CxcDocEncabezado::count());
        $this->assertSame(0, CxcAbono::count());
        $this->assertSame(2, CxcMovimiento::count());
        $this->assertEquals(-120000, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(120000, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
        $this->assertSame(0, DB::table('core_consecutivos_documentos')->count());
    }

    public function test_fallo_por_cuenta_origen_ausente_no_crea_documentos()
    {
        ContabMovimiento::where('core_tipo_transaccion_id', 8)->delete();
        try {
            $this->apply();
            $this->fail('Debe exigir la cuenta real del anticipo.');
        } catch (RuntimeException $error) {
            $this->assertContains('cuenta contable del anticipo', $error->getMessage());
        }
        $this->assertSame(0, ContabDocEncabezado::count());
        $this->assertSame(0, CxcDocEncabezado::count());
        $this->assertEquals(-120000, $this->advance->fresh()->saldo_pendiente);
    }

    public function test_rechaza_valor_superior_al_saldo_y_suma_duplicados()
    {
        $this->setExpectedException('InvalidArgumentException', 'saldo disponible suficiente');
        $objects = $this->advances(70000) . ',' . $this->advances(70000);
        (new HotelAdvancePaymentService())->validate($objects, 1, 2173, 2176, 200000);
    }

    public function test_rechaza_anticipo_de_otra_empresa()
    {
        $this->setExpectedException('InvalidArgumentException', 'empresa del pedido');
        (new HotelAdvancePaymentService())->validate($this->advances(), 2, 2173, 2176, 120000);
    }

    public function test_rechaza_anticipo_de_otro_huesped()
    {
        $this->setExpectedException('InvalidArgumentException', 'no pertenece al huésped');
        (new HotelAdvancePaymentService())->validate($this->advances(), 1, 999, 2176, 120000);
    }

    public function test_rechaza_anticipo_superior_al_total_factura()
    {
        $this->setExpectedException('InvalidArgumentException', 'superan el total');
        (new HotelAdvancePaymentService())->validate($this->advances(), 1, 2173, 2176, 100000);
    }

    public function test_reintento_no_reutiliza_un_anticipo_ya_consumido()
    {
        $this->apply();
        try {
            $this->apply();
            $this->fail('No debe reutilizar el anticipo.');
        } catch (InvalidArgumentException $error) {
            $this->assertContains('saldo disponible', $error->getMessage());
        }
        $this->assertSame(1, ContabDocEncabezado::count());
        $this->assertSame(2, CxcDocEncabezado::count());
    }

    public function test_nota_no_se_puede_anular_si_solo_sigue_activo_el_cruce_empresa()
    {
        $result = $this->apply();
        CxcAbono::where('core_tercero_id', 2173)->delete();
        $validation = (new ContabilidadController())->verificar_estados_lineas_registros($result['note'], $result['note']->lineas_registros);
        $this->assertFalse($validation['permitir_eliminar']);
        $this->assertSame(array(), $validation['ids_registros_cxc_eliminar']);
    }

    public function test_reversion_de_los_dos_cruces_restaura_saldos_y_conserva_la_nota()
    {
        $container = Container::getInstance();
        $container->instance('request', new Illuminate\Http\Request());
        $response = Mockery::mock();
        $response->shouldReceive('with')->andReturnSelf();
        $redirector = Mockery::mock();
        $redirector->shouldReceive('to')->andReturn($response);
        $container->instance('redirect', $redirector);
        Schema::create('teso_recaudos_libretas', function ($table) {
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo');
            $table->double('valor_recaudo');
        });

        $result = $this->apply();
        (new DocCruceController())->anular_doc_cruce($result['invoice_cross']->id);
        $this->assertEquals(120000, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));
        $this->assertEquals(-120000, CxcMovimiento::where('core_tipo_transaccion_id', 9)->where('core_tercero_id', 2176)->value('saldo_pendiente'));
        $validation = (new ContabilidadController())->verificar_estados_lineas_registros($result['note'], $result['note']->lineas_registros);
        $this->assertFalse($validation['permitir_eliminar']);

        (new DocCruceController())->anular_doc_cruce($result['source_cross']->id);
        $this->assertEquals(-120000, $this->advance->fresh()->saldo_pendiente);
        $this->assertEquals(120000, CxcMovimiento::where('core_tipo_transaccion_id', 9)->where('core_tercero_id', 2173)->value('saldo_pendiente'));
        $this->assertSame(0, CxcAbono::count());
        $this->assertEquals('Activo', $result['note']->fresh()->estado);
        $this->assertSame(4, CxcMovimiento::count());
        $this->assertSame(2, CxcDocEncabezado::where('estado', 'Anulado')->count());
        $validation = (new ContabilidadController())->verificar_estados_lineas_registros($result['note'], $result['note']->lineas_registros);
        $this->assertTrue($validation['permitir_eliminar']);
        $this->assertCount(2, $validation['ids_registros_cxc_eliminar']);
    }

    public function test_medio_de_pago_anticipo_debe_coincidir_con_la_seleccion()
    {
        $method = new ReflectionMethod(HotelService::class, 'validateAdvancePaymentLines');
        $method->setAccessible(true);
        $method->invoke(new HotelService(), $this->advances(70000),
            '[{"teso_medio_recaudo_id":"0-Anticipo","valor":"$70000"},{"teso_medio_recaudo_id":"1-Efectivo","valor":"$50000"}]');
        $this->setExpectedException('Exception', 'no coincide');
        $method->invoke(new HotelService(), $this->advances(70000),
            '[{"teso_medio_recaudo_id":"0-Anticipo","valor":"$120000"}]');
    }

    public function test_anticipo_140000_mas_efectivo_2800_con_motivo_configurado_inexistente()
    {
        $advance = $this->movement(8, 3, 8, 2173, -140000, 219);
        DB::table('cxc_movimientos')->where('core_tipo_transaccion_id', 47)
            ->update(array('valor_documento' => 142800, 'saldo_pendiente' => 142800));
        $this->invoice->valor_total = 142800;
        $this->invoice->turno_operativo_id = 0;
        $this->invoice->pdv_id = 1;
        $this->invoice->cliente_id = 2;

        (new HotelAdvancePaymentService())->apply($this->invoice,
            json_encode(array('cxc_movimiento_id' => $advance->id, 'valor_aplicar' => -140000)), 2173, 'HOT-343');
        $this->assertEquals(2800, CxcMovimiento::where('core_tipo_transaccion_id', 47)->value('saldo_pendiente'));

        // Captura la solicitud de tesorería sin crear un recaudo real.
        $request = (new HotelPaymentRequestTestService())->crear_abonos_documento($this->invoice,
            '[{"teso_medio_recaudo_id":"0-Anticipo","teso_motivo_id":"0-Cruce anticipos","teso_caja_id":"0-","valor":"$140000"},'
            . '{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"1-Caja mañana","valor":"$2800"}]');
        $cash = json_decode($request->lineas_registros_efectivo, true);
        $this->assertEquals(1, $cash[0]['teso_motivo_id_efectivo']);
        $this->assertEquals(2800, $cash[0]['valor_efectivo']);
        $this->assertEquals(2800, json_decode($request->lineas_registros, true)[0]['abono']);
        $this->assertSame(2, CxcDocEncabezado::count());
    }

    public function test_motivo_configurado_valido_tiene_prioridad()
    {
        DB::table('teso_motivos')->insert(array('id' => 4, 'movimiento' => 'entrada', 'teso_tipo_motivo' => 'recaudo-cartera'));
        $result = (new TreasuryService())->get_lineas_registros_medios_recaudos(
            '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"1-Caja","valor":"$2800"}]');
        $cash = json_decode('[' . $result->lineas_registros_efectivo . ']', true);
        $this->assertEquals(4, $cash[0]['teso_motivo_id_efectivo']);
    }

    public function test_motivo_configurado_de_salida_no_reemplaza_el_recaudo_seleccionado()
    {
        DB::table('teso_motivos')->insert(array('id' => 4, 'movimiento' => 'salida', 'teso_tipo_motivo' => 'pago-proveedores'));
        $result = (new TreasuryService())->get_lineas_registros_medios_recaudos(
            '[{"teso_medio_recaudo_id":"1-Transferencia","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"0-","teso_cuenta_bancaria_id":"2-Banco","valor":"$2800"}]');
        $bank = json_decode('[' . $result->lineas_registros_transferencia_consignacion . ']', true);
        $this->assertEquals(1, $bank[0]['teso_motivo_id_transferencia_consignacion']);
        $this->assertEquals(2800, $result->valor_abono);
    }

    public function test_falta_de_motivos_validos_da_un_mensaje_claro()
    {
        try {
            (new TreasuryService())->get_lineas_registros_medios_recaudos(
                '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"999-No existe","teso_caja_id":"1-Caja","valor":"$2800"}]');
            $this->fail('Debe explicar los motivos inexistentes.');
        } catch (RuntimeException $error) {
            $this->assertContains('configurado en Tesorería para recaudos de CxC (ID 4) no existe', $error->getMessage());
            $this->assertContains('seleccionado en el medio de pago (ID 999) no existe', $error->getMessage());
            $this->assertContains('columna Motivo', $error->getMessage());
        }
    }

    public function test_pago_solo_con_anticipo_no_requiere_motivo_de_tesoreria()
    {
        $this->assertFalse((new TreasuryService())->crear_abonos_documento($this->invoice,
            '[{"teso_medio_recaudo_id":"0-Anticipo","teso_motivo_id":"0-Cruce anticipos","valor":"$120000"}]'));
    }

    public function test_anulacion_de_factura_con_nota_de_traslado_muestra_restriccion_sin_excepcion()
    {
        $result = $this->apply();
        DB::table('core_tipos_docs_apps')->insert(array('id' => 18, 'prefijo' => 'FE'));
        $id = DB::table('vtas_doc_encabezados')->insertGetId(array('core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 47, 'core_tipo_doc_app_id' => 18, 'consecutivo' => 42, 'estado' => 'Activo'));

        $restriction = (new SalesDocumentHeaderService())->cancel_document_by_id($id, true);
        $this->assertEquals('mensaje_error', $restriction->status);
        $this->assertContains('Factura FE 42 NO puede ser anulada', $restriction->message);
        $this->assertContains('NC ' . $result['note']->consecutivo, $restriction->message);
        $this->assertContains('documentos de cruce asociados', $restriction->message);
        $this->assertEquals('Activo', VtasDocEncabezado::find($id)->estado);
        $this->assertSame(2, CxcAbono::count());
    }
}

class HotelPaymentRequestTestService extends TreasuryService
{
    protected function storeReceivablePayment(Illuminate\Http\Request $request)
    {
        return $request;
    }
}

class ElectronicNoteOriginFormTestService extends App\Core\Services\TurnoFormService
{
    protected function options($empresaId, $module, $persistedTurnId, $isEdit)
    {
        return array(10 => 'Turno de la factura');
    }
    protected function selectionLockedForCurrentUser() { return false; }
    protected function canRegisterAdjustment() { return true; }
    protected function hasTurnosScope($empresaId, $module) { return true; }

}

class HotelTransferDisplayTestInvoice
{
    public function __construct(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }

    public function get_label_documento()
    {
        return 'POS ' . $this->consecutivo;
    }
}
