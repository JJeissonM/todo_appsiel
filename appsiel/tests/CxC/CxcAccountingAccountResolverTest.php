<?php

use App\CxC\CxcMovimiento;
use App\CxC\Services\CxcAccountingAccountResolver;
use App\Http\Controllers\CxC\DocCruceController;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CxcAccountingAccountResolverTest extends PHPUnit_Framework_TestCase
{
    protected $db;

    protected function setUp()
    {
        parent::setUp();

        $this->db = new Capsule();
        $this->db->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();

        $this->db->schema()->create('cxc_movimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->string('consecutivo');
            $table->integer('core_tercero_id');
            $table->double('valor_documento');
        });

        $this->db->schema()->create('contab_movimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tipo_transaccion_id');
            $table->integer('core_tipo_doc_app_id');
            $table->string('consecutivo');
            $table->integer('core_tercero_id');
            $table->integer('contab_cuenta_id');
            $table->double('valor_debito');
            $table->double('valor_credito');
            $table->string('tipo_transaccion');
        });
    }

    public function testResolvesReceivableAndAdvanceAccountsByAccountingNature()
    {
        $receivable = $this->insertCxcMovement(23, 8, 15, 75000);
        $advance = $this->insertCxcMovement(8, 5, 20, -75000);

        $this->insertAccountingLine(23, 8, 15, 41, 75000, 0, 'factura_ventas');
        $this->insertAccountingLine(23, 8, 15, 81, 0, -75000, 'ingreso');
        $this->insertAccountingLine(8, 5, 20, 2, 75000, 0, 'recaudo');
        $this->insertAccountingLine(8, 5, 20, 54, 0, -75000, 'saldo_a_favor');

        $resolver = new CxcAccountingAccountResolver();

        $this->assertSame(41, $resolver->getReceivableAccountId($receivable));
        $this->assertSame(54, $resolver->getAdvanceAccountId($advance));
    }

    public function testCruceRejectsAnUnresolvedAccountingAccount()
    {
        $this->setExpectedException(
            'RuntimeException',
            'No se pudo determinar la cuenta contable del documento involucrado en el cruce de CxC.'
        );

        (new DocCruceController())->contabilizar_registro(null, '', 100, 0);
    }

    protected function insertCxcMovement($transactionId, $documentTypeId, $consecutive, $value)
    {
        $id = $this->db->table('cxc_movimientos')->insertGetId([
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => $transactionId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'consecutivo' => $consecutive,
            'core_tercero_id' => 4,
            'valor_documento' => $value,
        ]);

        return CxcMovimiento::find($id);
    }

    protected function insertAccountingLine($transactionId, $documentTypeId, $consecutive, $accountId, $debit, $credit, $type)
    {
        $this->db->table('contab_movimientos')->insert([
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => $transactionId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'consecutivo' => $consecutive,
            'core_tercero_id' => 4,
            'contab_cuenta_id' => $accountId,
            'valor_debito' => $debit,
            'valor_credito' => $credit,
            'tipo_transaccion' => $type,
        ]);
    }
}
