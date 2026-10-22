<?php

use App\Core\TipoDocApp;
use App\Core\EncabezadoDocumentoTransaccion;
use App\Core\Services\DocumentSequenceService;
use App\Core\Services\DocumentSequenceTransaction;
use App\Core\Transactions\Services\DocumentsService;
use App\Core\Transactions\TransactionDocumentHeader;
use App\Sistema\Modelo;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;

class SequenceTestHeader extends Model
{
    protected $table = 'vtas_pos_doc_encabezados';
    protected $guarded = array();
    public $timestamps = false;
}

/** No application bootstrap or .env: all tables live in an isolated database. */
class DocumentSequenceServiceTest extends PHPUnit_Framework_TestCase
{
    private $oldContainer;
    private $oldFacadeApplication;
    private $oldResolver;
    private $oldDispatcher;

    protected function setUp()
    {
        $this->oldContainer = Container::getInstance() ?: new Container();
        $this->oldFacadeApplication = Facade::getFacadeApplication();
        $this->oldResolver = Model::getConnectionResolver();
        $this->oldDispatcher = Model::getEventDispatcher();
        $container = new Container();
        $capsule = new Capsule($container);
        $capsule->addConnection(array('driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''));
        $capsule->setEventDispatcher(new Dispatcher($container));
        $capsule->bootEloquent();
        $container->instance('db', $capsule->getDatabaseManager());
        $container->bind('db.schema', function () use ($capsule) {
            return $capsule->getConnection()->getSchemaBuilder();
        });
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();
        Model::clearBootedModels();

        Schema::create('core_tipos_docs_apps', function ($table) {
            $table->increments('id');
        });
        DB::table('core_tipos_docs_apps')->insert(array(array('id' => 18), array('id' => 19)));
        Schema::create('core_consecutivos_documentos', function ($table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_documento_app_id');
            $table->integer('consecutivo_actual');
            $table->timestamps();
        });
        Schema::create('vtas_pos_doc_encabezados', function ($table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo');
            $table->string('uniqid')->nullable()->unique();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('sys_modelos', function ($table) {
            $table->increments('id');
            $table->string('modelo');
            $table->string('name_space');
        });
        DB::table('sys_modelos')->insert(array('id' => 230, 'modelo' => 'sequence_test', 'name_space' => SequenceTestHeader::class));
        require_once __DIR__.'/../../database/migrations/2026_10_09_000001_make_document_counters_unique.php';
        require_once __DIR__.'/../../database/migrations/2026_10_09_000002_make_pos_document_identity_unique.php';
        (new MakeDocumentCountersUnique())->up();
        (new MakePosDocumentIdentityUnique())->up();
    }

    protected function tearDown()
    {
        DB::purge();
        if ($this->oldResolver !== null) {
            Model::setConnectionResolver($this->oldResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        if ($this->oldDispatcher !== null) {
            Model::setEventDispatcher($this->oldDispatcher);
        } else {
            Model::unsetEventDispatcher();
        }
        Model::clearBootedModels();
        Container::setInstance($this->oldContainer);
        Facade::setFacadeApplication($this->oldFacadeApplication);
        Facade::clearResolvedInstances();
    }

    public function test_reserves_distinct_numbers_and_keeps_company_and_type_independent()
    {
        $this->assertSame(1, TipoDocApp::reservar_consecutivo(1, 18));
        $this->assertSame(2, TipoDocApp::reservar_consecutivo(1, 18));
        $this->assertSame(1, TipoDocApp::reservar_consecutivo(2, 18));
        $this->assertSame(1, TipoDocApp::reservar_consecutivo(1, 19));
        $this->assertSame(2, TipoDocApp::get_consecutivo_actual(1, 18));
    }

    public function test_preview_does_not_create_or_advance_a_counter()
    {
        $this->assertSame(0, TipoDocApp::get_consecutivo_actual(1, 18));
        $this->assertSame(0, DB::table('core_consecutivos_documentos')->count());
    }

    public function test_outer_rollback_reverts_new_and_existing_counter_reservations()
    {
        TipoDocApp::reservar_consecutivo(1, 18);
        DB::beginTransaction();
        $this->assertSame(2, TipoDocApp::reservar_consecutivo(1, 18));
        $this->assertSame(1, TipoDocApp::reservar_consecutivo(1, 19));
        $this->assertSame(1, DB::connection()->transactionLevel());
        DB::rollBack();
        $this->assertSame(1, TipoDocApp::get_consecutivo_actual(1, 18));
        $this->assertSame(0, TipoDocApp::get_consecutivo_actual(1, 19));
        $this->assertSame(2, TipoDocApp::reservar_consecutivo(1, 18));
    }

    public function test_all_header_factories_insert_the_reserved_number_without_a_placeholder()
    {
        SequenceTestHeader::creating(function ($header) {
            if ((int)$header->consecutivo !== TipoDocApp::get_consecutivo_actual(1, 18)) {
                throw new RuntimeException('Header event received a provisional number');
            }
        });
        $first = (new EncabezadoDocumentoTransaccion(230))->crear_nuevo($this->headerData());
        $second = (new DocumentsService('sequence_test'))->store_document_header($this->headerData());
        $thirdFactory = new TransactionDocumentHeader(Modelo::find(230));
        $thirdFactory->create($this->headerData());
        $this->assertSame(1, (int)$first->consecutivo);
        $this->assertSame(2, (int)$second->consecutivo);
        $this->assertSame(3, (int)$thirdFactory->document_header->consecutivo);
        $this->assertSame(3, DB::table('vtas_pos_doc_encabezados')->count());
    }

    public function test_rejected_header_insert_rolls_back_its_reservation()
    {
        $factory = new EncabezadoDocumentoTransaccion(230);
        $data = $this->headerData();
        $data['uniqid'] = 'same-request';
        $factory->crear_nuevo($data);
        try {
            $factory->crear_nuevo($data);
            $this->fail('Duplicate request should fail');
        } catch (QueryException $error) {
            $this->assertSame(1, DB::table('vtas_pos_doc_encabezados')->count());
            $this->assertSame(1, TipoDocApp::get_consecutivo_actual(1, 18));
            $this->assertSame(0, DB::connection()->transactionLevel());
        }
    }

    public function test_uniqueness_uses_all_four_document_fields()
    {
        $data = $this->headerData();
        $data['consecutivo'] = 7;
        DB::table('vtas_pos_doc_encabezados')->insert($data);
        foreach (array('core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id') as $column) {
            $other = $data;
            $other[$column]++;
            DB::table('vtas_pos_doc_encabezados')->insert($other);
        }
        try {
            DB::table('vtas_pos_doc_encabezados')->insert($data);
            $this->fail('Same document identity should be rejected');
        } catch (QueryException $error) {
            $this->assertSame(4, DB::table('vtas_pos_doc_encabezados')->count());
        }
    }

    public function test_unique_migrations_are_repeatable_and_reject_historical_duplicates()
    {
        (new MakeDocumentCountersUnique())->up();
        (new MakePosDocumentIdentityUnique())->up();
        (new MakeDocumentCountersUnique())->down();
        $row = array('core_empresa_id' => 1, 'core_documento_app_id' => 18, 'consecutivo_actual' => 20);
        DB::table('core_consecutivos_documentos')->insert(array($row, $row));
        try {
            (new MakeDocumentCountersUnique())->up();
            $this->fail('Historical duplicates must not be silently merged');
        } catch (RuntimeException $error) {
            $this->assertSame(2, DB::table('core_consecutivos_documentos')->count());
        }
        try {
            TipoDocApp::reservar_consecutivo(1, 18);
            $this->fail('An ambiguous counter must not advance');
        } catch (RuntimeException $error) {
            $this->assertSame(20, (int)DB::table('core_consecutivos_documentos')->first()->consecutivo_actual);
        }
        (new MakePosDocumentIdentityUnique())->down();
        DB::table('vtas_pos_doc_encabezados')->insert(array($this->headerData(), $this->headerData()));
        try {
            (new MakePosDocumentIdentityUnique())->up();
            $this->fail('Historical invoices must not be silently renumbered');
        } catch (RuntimeException $error) {
            $this->assertSame(2, DB::table('vtas_pos_doc_encabezados')->count());
        }
    }

    public function test_owned_operation_retries_lock_conflicts_after_rolling_back_all_writes()
    {
        $attempts = 0;
        $number = DocumentSequenceTransaction::run(function () use (&$attempts) {
            $attempts++;
            $number = DocumentSequenceService::reserve(1, 18);
            if ($attempts < 3) {
                throw $this->lockTimeout();
            }
            return $number;
        });
        $this->assertSame(3, $attempts);
        $this->assertSame(1, $number);
        $this->assertSame(1, TipoDocApp::get_consecutivo_actual(1, 18));
    }

    public function test_enclosing_operation_is_not_partially_retried_or_committed()
    {
        $attempts = 0;
        DB::beginTransaction();
        try {
            DocumentSequenceTransaction::run(function () use (&$attempts) {
                $attempts++;
                DocumentSequenceService::reserve(1, 18);
                throw $this->lockTimeout();
            });
            $this->fail('Lock conflict must reach the enclosing transaction');
        } catch (PDOException $error) {
            $this->assertSame(1, $attempts);
            $this->assertSame(1, DB::connection()->transactionLevel());
            DB::rollBack();
            $this->assertSame(0, TipoDocApp::get_consecutivo_actual(1, 18));
        }
    }

    public function test_invalid_or_nonexistent_type_cannot_create_a_counter()
    {
        foreach (array(array(0, 18), array(1, 0), array(1, 9999)) as $args) {
            try {
                DocumentSequenceService::reserve($args[0], $args[1]);
                $this->fail('Invalid reserve must fail');
            } catch (InvalidArgumentException $error) {
                $this->assertSame(0, DB::table('core_consecutivos_documentos')->count());
            }
        }
    }

    public function test_legacy_split_increment_is_rejected_without_changing_the_counter()
    {
        TipoDocApp::reservar_consecutivo(1, 18);
        try {
            TipoDocApp::aumentar_consecutivo(1, 18);
            $this->fail('Legacy split reservation must fail explicitly');
        } catch (LogicException $error) {
            $this->assertSame(1, TipoDocApp::get_consecutivo_actual(1, 18));
        }
    }

    public function test_exhausted_counter_and_functional_errors_do_not_advance_or_retry()
    {
        TipoDocApp::reservar_consecutivo(1, 18);
        DB::table('core_consecutivos_documentos')->update(array('consecutivo_actual' => 2147483647));
        try {
            TipoDocApp::reservar_consecutivo(1, 18);
            $this->fail('Exhausted integer sequence must fail');
        } catch (OverflowException $error) {
            $this->assertSame(2147483647, TipoDocApp::get_consecutivo_actual(1, 18));
        }
        $attempts = 0;
        try {
            DocumentSequenceTransaction::run(function () use (&$attempts) {
                $attempts++;
                TipoDocApp::reservar_consecutivo(1, 19);
                throw new InvalidArgumentException('Business validation failed');
            });
            $this->fail('Functional error must propagate');
        } catch (InvalidArgumentException $error) {
            $this->assertSame(1, $attempts);
            $this->assertSame(0, TipoDocApp::get_consecutivo_actual(1, 19));
        }
    }

    private function headerData()
    {
        return array('core_empresa_id' => 1, 'core_tipo_transaccion_id' => 47, 'core_tipo_doc_app_id' => 18, 'consecutivo' => 0);
    }

    private function lockTimeout()
    {
        $error = new PDOException('Simulated lock timeout');
        $error->errorInfo = array('HY000', 1205, 'Lock wait timeout');
        return $error;
    }
}
