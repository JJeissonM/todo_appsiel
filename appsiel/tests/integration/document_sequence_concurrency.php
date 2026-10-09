<?php

// Standalone integration test; never boot the application or load its .env.
// Requires a disposable MySQL database named appsiel_sequence_test_*.
require __DIR__.'/../../vendor/autoload.php';
spl_autoload_register(function ($class) {
    if (strpos($class, 'App\\') === 0) {
        $path = __DIR__.'/../../app/'.str_replace('\\', '/', substr($class, 4)).'.php';
        if (is_file($path)) {
            require $path;
        }
    }
});

use App\Core\Services\DocumentSequenceService;
use App\Core\Services\DocumentSequenceTransaction;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

function verifySequence($condition, $message)
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$database = getenv('SEQUENCE_TEST_DATABASE');
verifySequence(is_string($database) && preg_match('/^appsiel_sequence_test_[a-z0-9_]+$/', $database), 'An explicitly named disposable database is required');
$container = new Container();
$capsule = new Capsule($container);
$capsule->addConnection(array(
    'driver' => 'mysql',
    'host' => getenv('SEQUENCE_TEST_HOST') ?: '127.0.0.1',
    'port' => getenv('SEQUENCE_TEST_PORT') ?: '3306',
    'database' => $database,
    'username' => getenv('SEQUENCE_TEST_USERNAME'),
    'password' => getenv('SEQUENCE_TEST_PASSWORD'),
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
));
$container->instance('db', $capsule->getDatabaseManager());
$container->bind('db.schema', function () use ($capsule) {
    return $capsule->getConnection()->getSchemaBuilder();
});
Facade::setFacadeApplication($container);
$isolation = getenv('SEQUENCE_TEST_ISOLATION') ?: 'READ COMMITTED';
verifySequence(in_array($isolation, array('READ COMMITTED', 'REPEATABLE READ'), true), 'Unsupported test isolation');
DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL '.$isolation);

function waitSequenceFile($path)
{
    $deadline = microtime(true) + 15;
    while (!file_exists($path)) {
        verifySequence(microtime(true) < $deadline, 'Barrier timeout: '.$path);
        usleep(10000);
    }
}

function insertSequenceHeader($company, $type, $number)
{
    DB::table('vtas_pos_doc_encabezados')->insert(array(
        'core_empresa_id' => $company,
        'core_tipo_transaccion_id' => 47,
        'core_tipo_doc_app_id' => $type,
        'consecutivo' => $number,
    ));
}

if (isset($argv[1]) && $argv[1] === '--worker') {
    $mode = $argv[2];
    $barrier = $argv[3];
    $index = (int)$argv[4];
    touch($barrier.'.ready.'.$index);
    waitSequenceFile($barrier);
    $attempts = 0;
    if ($mode === 'deadlock') {
        $first = $index === 0 ? 18 : 19;
        $second = $index === 0 ? 19 : 18;
        DocumentSequenceTransaction::run(function () use ($first, $second, $barrier, $index, &$attempts) {
            $attempts++;
            $a = DocumentSequenceService::reserve(1, $first);
            if ($attempts === 1) {
                touch($barrier.'.locked.'.$index);
                waitSequenceFile($barrier.'.locked.'.(1 - $index));
            }
            $b = DocumentSequenceService::reserve(1, $second);
            insertSequenceHeader(1, $first, $a);
            insertSequenceHeader(1, $second, $b);
        });
    } else {
        $company = $mode === 'independent' && $index % 3 === 1 ? 2 : 1;
        $type = $mode === 'independent' && $index % 3 === 2 ? 19 : 18;
        $iterations = $mode === 'single' ? 1 : 10;
        for ($i = 0; $i < $iterations; $i++) {
            DocumentSequenceTransaction::run(function () use ($company, $type) {
                insertSequenceHeader($company, $type, DocumentSequenceService::reserve($company, $type));
            });
        }
    }
    echo json_encode(array('attempts' => $attempts));
    exit(0);
}

function startSequenceWorkers($mode, $count)
{
    $barrier = sys_get_temp_dir().'/sequence_'.uniqid('', true);
    $workers = array();
    for ($i = 0; $i < $count; $i++) {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' --worker '.escapeshellarg($mode).' '.escapeshellarg($barrier).' '.$i;
        $process = proc_open($command, array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        verifySequence(is_resource($process), 'Cannot start worker');
        fclose($pipes[0]);
        $workers[] = array($process, $pipes);
    }
    foreach ($workers as $i => $worker) {
        waitSequenceFile($barrier.'.ready.'.$i);
    }
    touch($barrier);
    return array($workers, $barrier);
}

function finishSequenceWorkers($run)
{
    list($workers, $barrier) = $run;
    $results = array();
    foreach ($workers as $worker) {
        $output = stream_get_contents($worker[1][1]);
        $error = stream_get_contents($worker[1][2]);
        fclose($worker[1][1]);
        fclose($worker[1][2]);
        $exit = proc_close($worker[0]);
        if ($exit === -1 && isset($worker[2])) {
            $exit = $worker[2];
        }
        verifySequence($exit === 0, 'Worker failed: '.$error.$output);
        $results[] = json_decode($output, true);
    }
    foreach (glob($barrier.'*') as $path) {
        unlink($path);
    }
    return $results;
}

// Only these test-owned tables are reset in the explicitly disposable database.
DB::statement('DROP TABLE IF EXISTS vtas_pos_doc_encabezados');
DB::statement('DROP TABLE IF EXISTS core_consecutivos_documentos');
DB::statement('DROP TABLE IF EXISTS core_tipos_docs_apps');
DB::statement('CREATE TABLE core_tipos_docs_apps (id INT PRIMARY KEY) ENGINE=InnoDB');
DB::statement('CREATE TABLE core_consecutivos_documentos (id INT AUTO_INCREMENT PRIMARY KEY, core_empresa_id INT NOT NULL, core_documento_app_id INT NOT NULL, consecutivo_actual INT NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL) ENGINE=InnoDB');
DB::statement('CREATE TABLE vtas_pos_doc_encabezados (id INT AUTO_INCREMENT PRIMARY KEY, core_empresa_id INT NOT NULL, core_tipo_transaccion_id INT NOT NULL, core_tipo_doc_app_id INT NOT NULL, consecutivo INT NOT NULL) ENGINE=InnoDB');
DB::table('core_tipos_docs_apps')->insert(array(array('id' => 18), array('id' => 19)));
require __DIR__.'/../../database/migrations/2026_10_09_000001_make_document_counters_unique.php';
require __DIR__.'/../../database/migrations/2026_10_09_000002_make_pos_document_identity_unique.php';
(new MakeDocumentCountersUnique())->up();
(new MakePosDocumentIdentityUnique())->up();

finishSequenceWorkers(startSequenceWorkers('shared', 12));
$numbers = DB::table('vtas_pos_doc_encabezados')->orderBy('consecutivo')->pluck('consecutivo');
verifySequence(array_map('intval', $numbers) === range(1, 120), 'Shared series has gaps or duplicate numbers');
verifySequence(DB::table('core_consecutivos_documentos')->count() === 1, 'Concurrent creation produced multiple counters');
echo "PASS: 12 concurrent workers, 120 headers, one newly created counter\n";

finishSequenceWorkers(startSequenceWorkers('independent', 9));
verifySequence(DB::table('core_consecutivos_documentos')->count() === 3, 'Company/type counters were mixed');
echo "PASS: simultaneous companies and document types\n";

// One locked series must not hold up a different existing series.
DB::beginTransaction();
DocumentSequenceService::reserve(1, 18);
$other = startSequenceWorkers('independent', 3);
$deadline = microtime(true) + 3;
do {
    $status = proc_get_status($other[0][2][0]);
    if (!$status['running']) {
        $other[0][2][2] = $status['exitcode'];
        break;
    }
    usleep(10000);
} while (microtime(true) < $deadline);
$independentFinished = !$status['running'];
$sharedWaiting = proc_get_status($other[0][0][0])['running'];
DB::rollBack();
finishSequenceWorkers($other);
verifySequence($independentFinished && $sharedWaiting, 'Locks affected another series or failed to serialize the shared series');
echo "PASS: another series continues while the shared series waits\n";

$before = DB::table('vtas_pos_doc_encabezados')->count();
$results = finishSequenceWorkers(startSequenceWorkers('deadlock', 2));
verifySequence(max(array_column($results, 'attempts')) >= 2, 'No deadlock retry was exercised');
verifySequence(DB::table('vtas_pos_doc_encabezados')->count() === $before + 4, 'Deadlock recovery left partial or repeated documents');
foreach (DB::table('core_consecutivos_documentos')->get() as $counter) {
    $query = DB::table('vtas_pos_doc_encabezados')->where('core_empresa_id', $counter->core_empresa_id)->where('core_tipo_doc_app_id', $counter->core_documento_app_id);
    $values = array_map('intval', $query->orderBy('consecutivo')->pluck('consecutivo'));
    verifySequence($values === range(1, (int)$counter->consecutivo_actual), 'Rollback/retry broke the sequence');
}
echo "PASS: real InnoDB deadlock, complete retry, no partial writes or duplicates\n";
