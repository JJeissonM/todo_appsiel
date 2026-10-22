<?php

use App\Ventas\ApmPrintJob;
use App\Ventas\Services\ApmPrintQueueService;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Facade;

class ApmPagoCxpReprintTest extends PHPUnit_Framework_TestCase
{
    protected $db;
    protected $service;
    protected $previousFacadeApplication;

    protected function setUp()
    {
        parent::setUp();
        $this->previousFacadeApplication = Facade::getFacadeApplication();
        $this->db = new Capsule();
        $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $this->db->schema()->create('apm_print_jobs', function (Blueprint $table) {
            $table->increments('id');
            foreach (['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id',
                'consecutivo', 'apm_print_status_id', 'copy_number', 'attempts_count'] as $column) {
                $table->integer($column)->default(0);
            }
            foreach (['document_type', 'document_label', 'copy_label', 'printer_id', 'station_id',
                'payload_json', 'last_error', 'queued_by', 'printed_by', 'queued_at',
                'last_attempt_at', 'printed_at', 'retired_at'] as $column) {
                $table->text($column)->nullable();
            }
            $table->timestamps();
        });
        Facade::setFacadeApplication($this->db->getContainer());
        Auth::swap(Mockery::mock()->shouldReceive('user')->andReturn(null)->getMock());
        $this->service = new PagoCxpReprintQueueService();
    }

    protected function tearDown()
    {
        Auth::clearResolvedInstance('auth');
        Facade::setFacadeApplication($this->previousFacadeApplication);
        Mockery::close();
        $this->db->getConnection()->disconnect();
        parent::tearDown();
    }

    protected function createJob($status)
    {
        return ApmPrintJob::create([
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 33,
            'core_tipo_doc_app_id' => 5,
            'consecutivo' => 123,
            'document_type' => 'comprobante_egreso',
            'document_label' => 'CE-123',
            'apm_print_status_id' => $status,
            'copy_number' => 0,
            'copy_label' => 'ORIGINAL',
            'attempts_count' => 1,
            'last_error' => 'APM desconectado',
            'payload_json' => json_encode(['PrinterId' => 'tesoreria', 'Document' => ['egreso' => ['Number' => 'CE-123']]])
        ]);
    }

    public function testFailedReprintsReuseTheJobAcrossFailuresAndSuccess()
    {
        $original = $this->createJob(2);
        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $prepared = $this->service->prepareReprint($original->id);
            $this->assertEquals($original->id, $prepared['job']->id);
            $this->assertEquals(1, $prepared['job']->apm_print_status_id);
            $this->assertEquals(0, $prepared['job']->copy_number);
            $this->assertSame('ORIGINAL', $prepared['payload']['CopyLabel']);
            $this->assertSame('CE-123', $prepared['payload']['Document']['egreso']['Number']);
            $this->assertEquals($attempt, $prepared['job']->attempts_count);
            $this->assertEquals(1, ApmPrintJob::count());
            $this->service->markFailed($prepared['job']->id, 'APM desconectado');
        }
        $prepared = $this->service->prepareReprint($original->id, false, true);
        $printed = $this->service->markPrinted($prepared['job']->id);
        $this->assertEquals($original->id, $printed->id);
        $this->assertEquals(4, $printed->attempts_count);
        $this->assertCount(0, $this->service->getActiveJobs());
        $this->assertEquals(1, ApmPrintJob::count());
    }

    public function testPendingReprintReusesTheJob()
    {
        $original = $this->createJob(1);
        $prepared = $this->service->prepareReprint($original->id);
        $this->assertEquals($original->id, $prepared['job']->id);
        $this->assertEquals(1, ApmPrintJob::count());
    }

    public function testExplicitCopyStillCreatesANewJob()
    {
        $original = $this->createJob(2);
        $prepared = $this->service->prepareReprint($original->id, true);
        $this->assertNotEquals($original->id, $prepared['job']->id);
        $this->assertEquals(1, $prepared['job']->copy_number);
        $this->assertEquals(2, ApmPrintJob::count());
    }

    public function testPrintedJobStillCreatesANewCopy()
    {
        $original = $this->createJob(3);
        $prepared = $this->service->prepareReprint($original->id);
        $this->assertNotEquals($original->id, $prepared['job']->id);
        $this->assertEquals(1, $prepared['job']->copy_number);
        $this->assertEquals(2, ApmPrintJob::count());
    }
}

class PagoCxpReprintQueueService extends ApmPrintQueueService
{
    protected function getStatusId($code)
    {
        $statuses = ['pending' => 1, 'failed' => 2, 'printed' => 3, 'retired' => 4];
        return $statuses[$code];
    }

    protected function applyCurrentDeviceConfig(array $payload)
    {
        return $payload;
    }
}
