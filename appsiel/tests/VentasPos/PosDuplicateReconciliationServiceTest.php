<?php

require_once __DIR__.'/../../app/VentasPos/Services/PosDuplicateReconciliationService.php';

use App\VentasPos\Services\PosDuplicateReconciliationService;

if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

class ReconciliationFailingPdo extends PDO
{
    public $failOnUpdate = 0;
    public $updateCount = 0;
    public $beforeWriteTransaction;
    private $transactions = 0;

    #[\ReturnTypeWillChange]
    public function beginTransaction()
    {
        if (++$this->transactions === 2 && $this->beforeWriteTransaction) {
            call_user_func($this->beforeWriteTransaction, $this);
        }
        return parent::beginTransaction();
    }

    #[\ReturnTypeWillChange]
    public function prepare($query, $options = array())
    {
        if (strpos($query, 'UPDATE ') === 0 && ++$this->updateCount === $this->failOnUpdate) {
            throw new PDOException('Fallo de escritura simulado');
        }
        return parent::prepare($query, $options);
    }
}

/** No Laravel bootstrap or .env. Every test uses its own in-memory database. */
class PosDuplicateReconciliationServiceTest extends PHPUnit_Framework_TestCase
{
    private $pdo;
    private $directory;

    protected function setUp(): void
    {
        $this->pdo = $this->createPdo();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $this->pdo->sqliteCreateFunction('regexp', function ($pattern, $value) { return preg_match('/'.$pattern.'/', (string)$value); }, 2);
        }
        $this->directory = sys_get_temp_dir().'/appsiel-pos-test-'.uniqid('', true);
        $common = 'id INTEGER PRIMARY KEY, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, fecha TEXT, core_tercero_id INTEGER, creado_por TEXT, pdv_id INTEGER, cajero_id INTEGER, cliente_id INTEGER, remision_doc_encabezado_id INTEGER, referencia_tercero_id INTEGER, inv_producto_id INTEGER, cantidad REAL, precio_total REAL, valor_debito REAL, valor_credito REAL, valor_movimiento REAL, valor_documento REAL, valor_pagado REAL, descripcion TEXT, estado TEXT';
        foreach (array('vtas_pos_doc_encabezados', 'vtas_pos_movimientos', 'vtas_movimientos', 'teso_movimientos', 'contab_movimientos', 'cxc_movimientos', 'inv_doc_encabezados') as $table) {
            $extra = $table === 'vtas_pos_doc_encabezados' ? ', valor_total REAL, forma_pago TEXT, valor_ajuste_al_peso REAL, valor_total_bolsas REAL' : '';
            $this->pdo->exec('CREATE TABLE '.$table.' ('.$common.$extra.')');
        }
        $this->pdo->exec('CREATE TABLE vtas_pos_doc_registros (id INTEGER PRIMARY KEY, vtas_pos_doc_encabezado_id INTEGER, inv_producto_id INTEGER, cantidad REAL, precio_total REAL)');
        $this->pdo->exec('CREATE TABLE cxc_abonos (id INTEGER PRIMARY KEY, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, core_tercero_id INTEGER, referencia_tercero_id INTEGER, doc_cxc_transacc_id INTEGER, doc_cxc_tipo_doc_id INTEGER, doc_cxc_consecutivo INTEGER, abono REAL, descripcion TEXT)');
        $this->pdo->exec('CREATE TABLE core_consecutivos_documentos (id INTEGER PRIMARY KEY, core_empresa_id INTEGER, core_documento_app_id INTEGER, consecutivo_actual INTEGER)');
        $this->pdo->exec('INSERT INTO core_consecutivos_documentos VALUES (1, 1, 18, 1500)');
        $this->pdo->exec('CREATE TABLE core_tipos_docs_apps (id INTEGER PRIMARY KEY, prefijo TEXT)');
        $this->pdo->exec("INSERT INTO core_tipos_docs_apps VALUES (18, 'POS')");
        $this->invoice(1, 100, 100, 'caja1', 1, 10);
        $this->invoice(2, 100, 200, 'caja2', 2, 20);
    }

    protected function createPdo()
    {
        return new ReconciliationFailingPdo('sqlite::memory:');
    }

    protected function tearDown(): void
    {
        $this->cleanBackup();
    }

    private function cleanBackup()
    {
        if ($this->directory && is_dir($this->directory)) {
            foreach (glob($this->directory.'/*') as $file) {
                unlink($file);
            }
            rmdir($this->directory);
        }
    }

    public function testDiagnosisDoesNotWriteAndStoresPrivateBackup()
    {
        $before = $this->dump();
        $plan = $this->reconcile(false);
        $this->assertSame($before, $this->dump());
        $this->assertCount(1, $plan['documents']);
        $this->assertSame(101, $plan['documents'][0]['new']);
        $this->assertCount(6, $plan['changes']);
        $this->assertSame(0700, fileperms($this->directory) & 0777);
        $this->assertSame(0600, fileperms($this->directory.'/plan.json') & 0777);
        $manifest = json_decode(file_get_contents($this->directory.'/manifest.json'), true);
        $this->assertSame(hash_file('sha256', $this->directory.'/plan.json'), $manifest['plan.json']);
    }

    public function testApplyChangesOnlyPlannedColumnsAndIsIdempotent()
    {
        $before = $this->dump();
        $plan = $this->reconcile(true);
        foreach ($plan['changes'] as $change) {
            $before[$change['table']][$change['id']] = $change['after'];
        }
        $this->assertEquals($before, $this->dump());
        $this->assertSame('COMMITTED', $this->result()['status']);
        $this->assertFileExists($this->directory.'/verified.json');
        $this->cleanBackup();
        $this->assertCount(0, $this->reconcile(true)['documents']);
    }

    public function testCreditInvoiceMovesItsPaidReceivableAndPaymentReference()
    {
        $this->pdo->exec("UPDATE vtas_pos_doc_encabezados SET forma_pago='credito', valor_ajuste_al_peso=10 WHERE id=2");
        $this->pdo->exec('DELETE FROM teso_movimientos WHERE id=2');
        $row = $this->row('contab_movimientos', 22);
        $row['id'] = 2;
        $row['valor_documento'] = $row['valor_pagado'] = 210;
        $this->insert('cxc_movimientos', $row);
        $this->pdo->exec('UPDATE contab_movimientos SET valor_debito=210 WHERE id=22');
        $adjustment = $row;
        $adjustment['id'] = 23;
        $adjustment['inv_producto_id'] = 0;
        $adjustment['valor_debito'] = 0;
        $adjustment['valor_credito'] = -10;
        $this->insert('contab_movimientos', $adjustment);
        $this->insert('cxc_abonos', array('id'=>1, 'core_empresa_id'=>1, 'core_tipo_transaccion_id'=>32, 'core_tipo_doc_app_id'=>3, 'consecutivo'=>900,
            'core_tercero_id'=>20, 'referencia_tercero_id'=>2, 'doc_cxc_transacc_id'=>47, 'doc_cxc_tipo_doc_id'=>18, 'doc_cxc_consecutivo'=>100, 'abono'=>210));
        $this->reconcile(true);
        $this->assertEquals(101, $this->row('cxc_movimientos', 2)['consecutivo']);
        $this->assertEquals(210, $this->row('cxc_movimientos', 2)['valor_pagado']);
        $this->assertEquals(101, $this->row('cxc_abonos', 1)['doc_cxc_consecutivo']);
        $this->assertEquals(900, $this->row('cxc_abonos', 1)['consecutivo']);
    }

    public function testInventoryReferenceFollowsExactRemissionAndKeepsOwnNumber()
    {
        $this->insert('inv_doc_encabezados', array('id'=>1002, 'core_empresa_id'=>1, 'core_tipo_transaccion_id'=>10, 'core_tipo_doc_app_id'=>15, 'consecutivo'=>777, 'descripcion'=>'Remisión de POS 100'));
        $this->reconcile(true);
        $this->assertSame('Remisión de POS 101', $this->row('inv_doc_encabezados', 1002)['descripcion']);
        $this->assertEquals(777, $this->row('inv_doc_encabezados', 1002)['consecutivo']);
    }

    public function testOccupiedDestinationBlocksWholeRepair()
    {
        $this->invoice(3, 101, 25, 'caja3', 3, 30);
        $this->blocked('Destino ocupado');
    }

    public function testAmbiguousMovementsAreNeverAssignedByTimeOrAmounts()
    {
        foreach (array('vtas_pos_doc_encabezados','vtas_pos_movimientos','vtas_movimientos','teso_movimientos','contab_movimientos') as $table) {
            $this->pdo->exec("UPDATE $table SET creado_por='same', core_tercero_id=10");
        }
        $this->blocked('ambigua');
    }

    public function testUnbalancedAccountingBlocksWholeRepair()
    {
        $this->pdo->exec('UPDATE contab_movimientos SET valor_debito=201 WHERE id=22');
        $this->blocked('importes no cuadran');
    }

    public function testUnsupportedReferencesBlockWholeRepair()
    {
        $this->pdo->exec('CREATE TABLE custom_links (id INTEGER PRIMARY KEY, doc_origen_consecutivo INTEGER)');
        $this->pdo->exec('INSERT INTO custom_links VALUES(1,100)');
        $this->blocked('Referencia numérica adicional');
    }

    public function testUnsupportedTextBlocksWholeRepair()
    {
        $this->pdo->exec('CREATE TABLE custom_text (id INTEGER PRIMARY KEY, descripcion TEXT)');
        $this->pdo->exec("INSERT INTO custom_text VALUES(1,'Factura POS 100')");
        $this->blocked('Referencia textual no soportada');
    }

    public function testFailureAfterFirstWritesRollsBackEveryChange()
    {
        $this->pdo->failOnUpdate = 3;
        $before = $this->dump();
        try {
            $this->reconcile(true);
            $this->fail('Debe rechazar el fallo simulado');
        } catch (PDOException $error) {
            $this->assertSame('Fallo de escritura simulado', $error->getMessage());
        }
        $this->assertSame($before, $this->dump());
        $this->assertFalse($this->pdo->inTransaction());
        $this->assertFileExists($this->directory.'/plan.json');
        $this->assertFalse(file_exists($this->directory.'/result.json'));
    }

    public function testCancelledDuplicateWithNoMovementsCanBeRenumbered()
    {
        $this->pdo->exec("UPDATE vtas_pos_doc_encabezados SET estado='Anulado' WHERE id=1");
        foreach (array('vtas_pos_movimientos','vtas_movimientos','teso_movimientos') as $table) {
            $this->pdo->exec("DELETE FROM $table WHERE id=1");
        }
        $this->pdo->exec('DELETE FROM contab_movimientos WHERE id IN (11,12)');
        $this->reconcile(true);
        $this->assertEquals(101, $this->row('vtas_pos_doc_encabezados',1)['consecutivo']);
        $this->assertEquals(100, $this->row('vtas_pos_doc_encabezados',2)['consecutivo']);
    }

    public function testAllGroupsAreValidatedBeforeAnyWrite()
    {
        $this->invoice(3, 200, 50, 'caja3', 3, 30);
        $this->invoice(4, 200, 60, 'caja4', 4, 40);
        $this->pdo->exec('UPDATE vtas_pos_doc_registros SET precio_total=99 WHERE id=4');
        $this->blocked('Detalle');
    }

    public function testThreeDistinctInvoicesReceiveSeparateFreeNumbers()
    {
        $this->invoice(3, 100, 50, 'caja3', 3, 30);
        $plan = $this->reconcile(true);
        $this->assertCount(2, $plan['documents']);
        $this->assertEquals(101, $this->row('vtas_pos_doc_encabezados',2)['consecutivo']);
        $this->assertEquals(102, $this->row('vtas_pos_doc_encabezados',3)['consecutivo']);
    }

    public function testOtherDocumentSeriesWithSameNumbersRemainUnchanged()
    {
        $this->insert('inv_doc_encabezados', array('id'=>99, 'core_empresa_id'=>1, 'core_tipo_transaccion_id'=>10, 'core_tipo_doc_app_id'=>15, 'consecutivo'=>101, 'descripcion'=>''));
        $before = $this->row('inv_doc_encabezados',99);
        $this->reconcile(true);
        $this->assertSame($before, $this->row('inv_doc_encabezados',99));
    }

    public function testCounterBehindDestinationBlocksRepair()
    {
        $this->pdo->exec('UPDATE core_consecutivos_documentos SET consecutivo_actual=100');
        $this->blocked('supera el contador');
    }

    public function testDoesNotReusePlannedDestinationAcrossTransactions()
    {
        $this->invoice(3, 100, 50, 'caja3', 3, 30);
        $this->invoice(4, 100, 60, 'caja4', 4, 40);
        foreach (array('vtas_pos_doc_encabezados','vtas_pos_movimientos','vtas_movimientos','teso_movimientos') as $table) {
            $this->pdo->exec("UPDATE $table SET core_tipo_transaccion_id=48 WHERE id IN(3,4)");
        }
        $this->pdo->exec('UPDATE contab_movimientos SET core_tipo_transaccion_id=48 WHERE id IN(31,32,41,42)');
        $this->blocked('mismo destino');
    }

    public function testChangedDataBetweenPlanAndApplyAbortsWithoutOverwritingIt()
    {
        $this->pdo->beforeWriteTransaction = function ($pdo) {
            $pdo->exec("UPDATE teso_movimientos SET descripcion='Modificación concurrente' WHERE id=2");
        };
        try {
            $this->reconcile(true);
            $this->fail('Debe detectar cambios posteriores al respaldo');
        } catch (RuntimeException $error) {
            $this->assertNotFalse(strpos($error->getMessage(), 'datos cambiaron'));
        }
        $this->assertSame('Modificación concurrente', $this->row('teso_movimientos',2)['descripcion']);
        $this->assertEquals(100, $this->row('vtas_pos_doc_encabezados',2)['consecutivo']);
        $this->assertSame(0, $this->pdo->updateCount);
    }

    public function testDoesNotReuseAnExistingBackupDirectory()
    {
        mkdir($this->directory, 0700);
        file_put_contents($this->directory.'/sentinel', 'original');
        try {
            $this->reconcile(true);
            $this->fail('Debe proteger los respaldos previos');
        } catch (RuntimeException $error) {
            $this->assertNotFalse(strpos($error->getMessage(), 'ya existe'));
        }
        $this->assertSame('original', file_get_contents($this->directory.'/sentinel'));
    }

    private function blocked($message)
    {
        $before = $this->dump();
        try {
            $this->reconcile(true);
            $this->fail('Debe rechazar la conciliación');
        } catch (RuntimeException $error) {
            $this->assertNotFalse(strpos($error->getMessage(), $message), $error->getMessage());
        }
        $this->assertSame($before, $this->dump());
        $this->assertFalse($this->pdo->inTransaction());
    }

    private function reconcile($execute)
    {
        return (new PosDuplicateReconciliationService($this->pdo))->reconcile($execute, $this->directory);
    }

    private function result() { return json_decode(file_get_contents($this->directory.'/result.json'), true); }
    private function row($table, $id) { return $this->pdo->query('SELECT * FROM '.$table.' WHERE id='.(int)$id)->fetch(PDO::FETCH_ASSOC); }

    private function dump()
    {
        $result = array();
        $sql = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite'
            ? "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name"
            : 'SELECT TABLE_NAME FROM information_schema.tables WHERE TABLE_SCHEMA=DATABASE() ORDER BY TABLE_NAME';
        foreach ($this->pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $result[$table] = array();
            foreach ($this->pdo->query('SELECT * FROM '.$table.' ORDER BY id')->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $result[$table][$row['id']] = $row;
            }
        }
        return $result;
    }

    private function invoice($id, $number, $amount, $user, $pdv, $third)
    {
        $base = array('id'=>$id, 'core_empresa_id'=>1, 'core_tipo_transaccion_id'=>47, 'core_tipo_doc_app_id'=>18, 'consecutivo'=>$number,
            'fecha'=>'2026-07-01', 'core_tercero_id'=>$third, 'creado_por'=>$user, 'pdv_id'=>$pdv, 'cajero_id'=>$pdv, 'cliente_id'=>$id,
            'remision_doc_encabezado_id'=>1000+$id, 'referencia_tercero_id'=>$id, 'inv_producto_id'=>$id, 'cantidad'=>1,
            'precio_total'=>$amount, 'valor_debito'=>0, 'valor_credito'=>0, 'valor_movimiento'=>$amount, 'valor_documento'=>0, 'valor_pagado'=>0,
            'descripcion'=>'', 'estado'=>'Contabilizado');
        $header = $base + array('valor_total'=>$amount, 'forma_pago'=>'contado', 'valor_ajuste_al_peso'=>0, 'valor_total_bolsas'=>0);
        $this->insert('vtas_pos_doc_encabezados', $header);
        $this->insert('vtas_pos_doc_registros', array('id'=>$id,'vtas_pos_doc_encabezado_id'=>$id,'inv_producto_id'=>$id,'cantidad'=>1,'precio_total'=>$amount));
        foreach (array('vtas_pos_movimientos','vtas_movimientos','teso_movimientos') as $table) {
            $this->insert($table, $base);
        }
        $credit = $base;
        $credit['id'] = $id*10+1;
        $credit['valor_credito'] = -$amount;
        $this->insert('contab_movimientos', $credit);
        $debit = $base;
        $debit['id'] = $id*10+2;
        $debit['inv_producto_id'] = 0;
        $debit['valor_debito'] = $amount;
        $this->insert('contab_movimientos', $debit);
    }

    private function insert($table, array $row)
    {
        $stmt = $this->pdo->prepare('INSERT INTO '.$table.' ('.implode(',', array_keys($row)).') VALUES ('.implode(',', array_fill(0,count($row),'?')).')');
        $stmt->execute(array_values($row));
    }
}
