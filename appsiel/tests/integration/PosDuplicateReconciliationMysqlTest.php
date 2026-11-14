<?php

require_once __DIR__.'/../VentasPos/PosDuplicateReconciliationServiceTest.php';

/**
 * Optional local integration suite. Never loads .env or connects over TCP.
 * Creates/drops only its own per-process test database on the supplied Unix socket.
 */
class PosDuplicateReconciliationMysqlTest extends PosDuplicateReconciliationServiceTest
{
    private $localPdo;
    private $testDatabase;

    protected function createPdo()
    {
        $socket = getenv('APPSIEL_POS_TEST_MYSQL_SOCKET');
        if (!$socket) {
            $this->markTestSkipped('Defina APPSIEL_POS_TEST_MYSQL_SOCKET para probar en un servidor local aislado.');
        }
        $this->testDatabase = 'appsiel_pos_reconciliation_test_'.getmypid();
        $this->localPdo = new ReconciliationFailingPdo('mysql:unix_socket='.$socket.';charset=utf8mb4',
            getenv('APPSIEL_POS_TEST_MYSQL_USER') ?: 'root', getenv('APPSIEL_POS_TEST_MYSQL_PASSWORD') ?: '');
        $this->localPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->localPdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->localPdo->exec('CREATE DATABASE `'.$this->testDatabase.'`');
        $this->localPdo->exec('USE `'.$this->testDatabase.'`');
        return $this->localPdo;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if ($this->localPdo) {
            if ($this->localPdo->inTransaction()) {
                $this->localPdo->rollBack();
            }
            $this->localPdo->exec('DROP DATABASE `'.$this->testDatabase.'`');
            $this->localPdo = null;
        }
    }
}
