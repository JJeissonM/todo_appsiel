<?php

use App\CxP\CxpMovimiento;
use App\CxP\Services\CxpAccountingAccountResolver;
use App\Http\Controllers\CxP\DocCruceController;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

class CxpAccountingAccountResolverTest extends PHPUnit_Framework_TestCase
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

        $this->db->schema()->create('cxp_movimientos', function (Blueprint $table) {
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

    public function testResolvesAccountsWhenSourceUsesDifferentOrEmptyTransactionLabels()
    {
        $payable = $this->insertCxpMovement(25, 17, 2, 10000);
        $advance = $this->insertCxpMovement(17, 23, 2, -10000);

        $this->insertAccountingLine(25, 17, 2, 131, 0, -10000, 'factura_entrada_pendiente');
        $this->insertAccountingLine(17, 23, 2, 29, 10000, 0, '');
        $this->insertAccountingLine(17, 23, 2, 7, 0, -10000, '');

        $resolver = new CxpAccountingAccountResolver();

        $this->assertSame(131, $resolver->getPayableAccountId($payable));
        $this->assertSame(29, $resolver->getAdvanceAccountId($advance));
    }

    public function testCruceRejectsAnUnresolvedAccountingAccount()
    {
        $this->setExpectedException(
            'RuntimeException',
            'No se pudo determinar la cuenta contable del documento involucrado en el cruce de CxP.'
        );

        (new DocCruceController())->contabilizar_registro(null, '', 100, 0);
    }

    protected function insertCxpMovement($transactionId, $documentTypeId, $consecutive, $value)
    {
        $id = $this->db->table('cxp_movimientos')->insertGetId([
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => $transactionId,
            'core_tipo_doc_app_id' => $documentTypeId,
            'consecutivo' => $consecutive,
            'core_tercero_id' => 4,
            'valor_documento' => $value,
        ]);

        return CxpMovimiento::find($id);
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
