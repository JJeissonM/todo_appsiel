<?php
/**
 * Carga puntual autorizada: provisiones enero-mayo 2026 del Colegio Bilingüe.
 * php script.php --dry-run /directorio/privado (inserta, valida y ROLLBACK)
 * php script.php --execute /directorio/privado (valida y COMMIT)
 * Nunca actualiza nóminas, contratos, consolidados ni movimientos anteriores.
 */
date_default_timezone_set('America/Bogota');
$mode = isset($argv[1]) ? $argv[1] : '';
$folder = isset($argv[2]) ? $argv[2] : '';
if (!in_array($mode, ['--dry-run', '--execute'], true) || !is_dir($folder)) {
    throw new RuntimeException('Uso: script.php --dry-run|--execute directorio_soportes');
}
$raw = file_get_contents($folder . '/fuente.json');
$snapshot = json_decode($raw, true);
$plan = json_decode(file_get_contents($folder . '/plan.json'), true);
function ensure($condition, $message) {
    if (!$condition) { throw new RuntimeException($message); }
}
ensure(is_array($snapshot) && is_array($plan), 'Soportes JSON inválidos.');
ensure(hash('sha256', $raw) === $plan['source_sha256'], 'El snapshot no coincide con el plan.');
ensure($plan['database'] === 'u883985631_colegiobilingu', 'Destino no autorizado.');
ensure($plan['marca'] === 'Regularizacion provisiones nomina enero-mayo 2026', 'Marca inesperada.');
$env = parse_ini_file(dirname(__DIR__, 2) . '/.env', false, INI_SCANNER_RAW);
$pdo = new PDO('mysql:host=' . $env['DB_HOST'] . ';port=' . $env['DB_PORT'] .
    ';dbname=' . $env['DB_DATABASE'] . ';charset=utf8mb4', $env['DB_USERNAME'], $env['DB_PASSWORD'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 20,
    PDO::ATTR_EMULATE_PREPARES => false
]);
function allRows($sql, $args = []) {
    global $pdo;
    $stmt = $pdo->prepare($sql); $stmt->execute($args);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function sameRows($actual, $expected, $label) {
    $sort = function (&$rows) {
        usort($rows, function ($a, $b) { return (int)$a['id'] <=> (int)$b['id']; });
    };
    $sort($actual); $sort($expected);
    ensure($actual == $expected, 'La fuente cambió: ' . $label);
}
function insertOne($table, $row) {
    global $pdo;
    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($row)) .
           '`) VALUES (' . implode(',', array_fill(0, count($row), '?')) . ')';
    $pdo->prepare($sql)->execute(array_values($row));
    return (int)$pdo->lastInsertId();
}
function insertBatch($table, $rows) {
    global $pdo;
    foreach (array_chunk($rows, 150) as $chunk) {
        $fields = array_keys($chunk[0]); $args = [];
        foreach ($chunk as $row) {
            ensure(array_keys($row) === $fields, 'Columnas inconsistentes.');
            $args = array_merge($args, array_values($row));
        }
        $tuple = '(' . implode(',', array_fill(0, count($fields), '?')) . ')';
        $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $fields) . '`) VALUES ' .
               implode(',', array_fill(0, count($chunk), $tuple));
        $pdo->prepare($sql)->execute($args);
    }
}
ensure(allRows('SELECT DATABASE() db')[0]['db'] === $plan['database'], 'Base inesperada.');
$pdo->exec('SET SESSION innodb_lock_wait_timeout=15');
$pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
$pdo->beginTransaction();
try {
    $tables = ['contab_doc_encabezados','contab_doc_registros','contab_movimientos',
               'nom_contabilizaciones_procesos','core_consecutivos_documentos'];
    foreach ($tables as $table) {
        $engine = allRows('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?', [$table]);
        ensure(count($engine) === 1 && $engine[0]['ENGINE'] === 'InnoDB', 'Tabla no transaccional: ' . $table);
    }
    // Reserva serializada mediante el mismo contador que utiliza el sistema.
    $counter = allRows('SELECT * FROM core_consecutivos_documentos WHERE core_empresa_id=1 AND core_documento_app_id=15 FOR UPDATE');
    ensure(count($counter) === 1, 'Contador NI inexistente o duplicado.');
    sameRows(allRows("SELECT * FROM nom_doc_registros WHERE fecha BETWEEN '2026-01-01' AND '2026-05-31' ORDER BY id"), $snapshot['registros'], 'registros de nómina');
    sameRows(allRows("SELECT * FROM nom_doc_encabezados WHERE id IN (SELECT DISTINCT nom_doc_encabezado_id FROM nom_doc_registros WHERE fecha BETWEEN '2026-01-01' AND '2026-05-31') ORDER BY id"), $snapshot['documentos'], 'documentos de nómina');
    sameRows(allRows('SELECT * FROM nom_consolidados_prestaciones_sociales ORDER BY id'), $snapshot['consolidados'], 'consolidados de mayo');
    sameRows(allRows('SELECT * FROM nom_parametros_liquidacion_prestaciones_sociales ORDER BY id'), $snapshot['parametros'], 'parámetros');
    sameRows(allRows('SELECT * FROM nom_conceptos ORDER BY id'), $snapshot['conceptos'], 'conceptos');
    sameRows(allRows('SELECT * FROM nom_agrupacion_tiene_conceptos ORDER BY id'), $snapshot['agrupaciones'], 'agrupaciones');
    foreach (['contab_doc_encabezados', 'contab_movimientos', 'nom_contabilizaciones_procesos'] as $table) {
        ensure((int)allRows("SELECT COUNT(*) n FROM `$table` WHERE creado_por=?", [$plan['marca']])[0]['n'] === 0,
            'La regularización ya existe en ' . $table . '. No se duplicará.');
    }
    ensure((int)allRows("SELECT COUNT(*) n FROM contab_movimientos m JOIN contab_cuentas c ON c.id=m.contab_cuenta_id WHERE m.core_empresa_id=1 AND m.fecha BETWEEN '2026-01-01' AND '2026-05-31' AND c.codigo IN ('510530','510533','510536','510539','61600504','61600505','61600506','61600507')")[0]['n'] === 0,
        'Ya existen gastos/costos de provisiones en el periodo.');
    ensure((int)allRows("SELECT COUNT(*) n FROM nom_contabilizaciones_procesos WHERE core_empresa_id=1 AND proceso_contabilizado='provision_prestaciones_sociales' AND fecha BETWEEN '2026-01-01' AND '2026-05-31'")[0]['n'] === 0, 'El periodo ya está contabilizado.');
    sameRows(allRows("SELECT m.* FROM contab_movimientos m JOIN contab_cuentas c ON c.id=m.contab_cuenta_id WHERE c.codigo IN ('510530','510533','510536','510539','61600504','61600505','61600506','61600507','261005','261010','261015','261020') AND m.fecha <= '2026-05-31' ORDER BY m.id"), $snapshot['previos'], 'pasivos y gastos previos');
    $nit = $snapshot['empresa'][0]['numero_identificacion'];
    $company = allRows('SELECT id FROM core_terceros WHERE core_empresa_id=1 AND numero_identificacion=? ORDER BY id', [$nit]);
    ensure(count($company) === 1, 'Tercero de la empresa ambiguo.');
    $docType = allRows('SELECT * FROM core_tipos_docs_apps WHERE id=15');
    ensure(count($docType) === 1 && $docType[0]['estado'] === 'Activo', 'NI no activo.');
    $params = [];
    foreach ($snapshot['parametros'] as $param) { $params[$param['concepto_prestacion']] = $param; }
    $accountIds = [];
    $grouped = []; $keys = []; $totalPlan = 0; $expectedMovements = 0;
    foreach ($plan['filas'] as $row) {
        ensure(in_array($row['periodo'], ['2026-01','2026-02','2026-03','2026-04','2026-05'], true), 'Mes fuera del alcance.');
        ensure(substr($row['fecha'], 0, 7) === $row['periodo'], 'Fecha fuera del periodo.');
        ensure(is_int($row['valor']) && $row['valor'] >= 0, 'Importe inválido.');
        $key = $row['periodo'] . ':' . $row['nom_contrato_id'] . ':' . $row['prestacion'];
        ensure(!isset($keys[$key]), 'Provisión duplicada en el plan.'); $keys[$key] = true;
        $param = $params[$row['prestacion']];
        ensure((int)$param['contab_cuenta_db_id'] === $row['cuenta_db_id'] &&
               (int)$param['contab_cuenta_cr_id'] === $row['cuenta_cr_id'], 'Cuenta distinta del parámetro.');
        $accountIds[$row['cuenta_db_id']] = true; $accountIds[$row['cuenta_cr_id']] = true;
        $grouped[$row['periodo']][] = $row;
        $totalPlan += $row['valor'];
        if ($row['valor']) { $expectedMovements += 2; }
    }
    ensure(count($grouped) === 5 && $expectedMovements === $plan['movimientos'], 'Número de movimientos inválido.');
    foreach (array_keys($accountIds) as $id) {
        $account = allRows('SELECT core_empresa_id,estado FROM contab_cuentas WHERE id=?', [$id]);
        ensure(count($account) === 1 && (int)$account[0]['core_empresa_id'] === 1 && $account[0]['estado'] === 'Activo', 'Cuenta inválida: ' . $id);
    }
    // Guarda preimagen y plan antes de la primera escritura permanente.
    if ($mode === '--execute') {
        ensure(file_put_contents($folder . '/preimagen_ejecucion.json', json_encode([
            'counter' => $counter, 'marca' => $plan['marca'], 'plan_sha256' => hash_file('sha256', $folder . '/plan.json'),
            'consolidados_sha256' => hash('sha256', json_encode($snapshot['consolidados']))
        ], JSON_PRETTY_PRINT)) !== false, 'No se pudo guardar la preimagen.');
    }
    $maxHeader = (int)allRows('SELECT COALESCE(MAX(consecutivo),0) n FROM contab_doc_encabezados WHERE core_empresa_id=1 AND core_tipo_doc_app_id=15')[0]['n'];
    $maxMovement = (int)allRows('SELECT COALESCE(MAX(CAST(consecutivo AS UNSIGNED)),0) n FROM contab_movimientos WHERE core_empresa_id=1 AND core_tipo_doc_app_id=15')[0]['n'];
    $number = max((int)$counter[0]['consecutivo_actual'], $maxHeader, $maxMovement);
    $now = allRows('SELECT NOW() now')[0]['now']; $result = [];
    foreach ($grouped as $period => $rows) {
        ++$number;
        $amount = array_sum(array_column($rows, 'valor'));
        $description = 'Provisión prestaciones sociales ' . $period . '. Regularización histórica basada en nómina y criterio mayo 2026.';
        $header = ['core_tipo_transaccion_id'=>9,'core_tipo_doc_app_id'=>15,'consecutivo'=>$number,
            'fecha'=>$rows[0]['fecha'],'core_empresa_id'=>1,'core_tercero_id'=>(int)$company[0]['id'],
            'codigo_referencia_tercero'=>'','documento_soporte'=>'PROV-NOM-' . $period,
            'descripcion'=>$description,'valor_total'=>$amount,'estado'=>'Activo',
            'creado_por'=>$plan['marca'],'modificado_por'=>$plan['marca'],'created_at'=>$now,'updated_at'=>$now];
        $headerId = insertOne('contab_doc_encabezados', $header);
        insertOne('nom_contabilizaciones_procesos', [
            'core_tipo_transaccion_id'=>9,'core_tipo_doc_app_id'=>15,'consecutivo'=>$number,
            'fecha'=>$rows[0]['fecha'],'core_empresa_id'=>1,'proceso_contabilizado'=>'provision_prestaciones_sociales',
            'descripcion'=>$description,'estado'=>'Activo','creado_por'=>$plan['marca'],
            'modificado_por'=>$plan['marca'],'created_at'=>$now,'updated_at'=>$now]);
        $details = [];
        foreach ($rows as $row) {
            if (!$row['valor']) { continue; }
            foreach (['db','cr'] as $side) {
                $details[] = ['contab_doc_encabezado_id'=>$headerId,'contab_cuenta_id'=>$row['cuenta_' . $side . '_id'],
                    'core_tercero_id'=>$row['core_tercero_id'],'tipo_transaccion'=>'causacion',
                    'valor_debito'=>$side === 'db' ? $row['valor'] : 0,
                    'valor_credito'=>$side === 'cr' ? $row['valor'] : 0,
                    'detalle_operacion'=>'Provisión ' . $row['prestacion'] . ' ' . $period . '; contrato ' . $row['nom_contrato_id'],
                    'estado'=>'Activo','created_at'=>$now,'updated_at'=>$now];
            }
        }
        insertBatch('contab_doc_registros', $details);
        $storedDetails = allRows('SELECT * FROM contab_doc_registros WHERE contab_doc_encabezado_id=? ORDER BY id', [$headerId]);
        ensure(count($storedDetails) === count($details), 'Faltan líneas del documento.');
        $movements = [];
        foreach ($storedDetails as $detail) {
            $db = (int)$detail['valor_debito']; $cr = (int)$detail['valor_credito'];
            $movements[] = ['core_tipo_transaccion_id'=>9,'core_tipo_doc_app_id'=>15,'consecutivo'=>$number,
                'id_registro_doc_tipo_transaccion'=>(int)$detail['id'],'fecha'=>$rows[0]['fecha'],
                'core_empresa_id'=>1,'core_tercero_id'=>(int)$detail['core_tercero_id'],
                'codigo_referencia_tercero'=>'','documento_soporte'=>'PROV-NOM-' . $period,
                'contab_cuenta_id'=>(int)$detail['contab_cuenta_id'],'valor_operacion'=>$db+$cr,
                'valor_debito'=>$db,'valor_credito'=>-$cr,'valor_saldo'=>$db-$cr,
                'detalle_operacion'=>$detail['detalle_operacion'],'tipo_transaccion'=>'causacion',
                'inv_producto_id'=>0,'impuesto_id'=>null,'cantidad'=>0,'tasa_impuesto'=>0,'base_impuesto'=>0,
                'valor_impuesto'=>0,'fecha_vencimiento'=>$rows[0]['fecha'],'inv_bodega_id'=>0,
                'teso_caja_id'=>null,'teso_cuenta_bancaria_id'=>null,'estado'=>'Activo',
                'creado_por'=>$plan['marca'],'modificado_por'=>$plan['marca'],'created_at'=>$now,'updated_at'=>$now];
        }
        insertBatch('contab_movimientos', $movements);
        $balance = allRows('SELECT COUNT(*) n,SUM(valor_debito) db,SUM(valor_credito) cr,SUM(valor_saldo) saldo FROM contab_movimientos WHERE core_empresa_id=1 AND core_tipo_transaccion_id=9 AND core_tipo_doc_app_id=15 AND consecutivo=?', [$number])[0];
        ensure((int)$balance['n'] === count($details) && (int)$balance['db'] === $amount && (int)$balance['cr'] === -$amount && (int)$balance['saldo'] === 0,
            'Documento descuadrado: NI ' . $number);
        $result[] = ['periodo'=>$period,'fecha'=>$rows[0]['fecha'],'encabezado_id'=>$headerId,
                     'documento'=>'NI ' . $number,'movimientos'=>count($details),'debito'=>$amount,'credito'=>$amount];
    }
    $pdo->prepare('UPDATE core_consecutivos_documentos SET consecutivo_actual=?,updated_at=? WHERE id=?')->execute([$number,$now,$counter[0]['id']]);
    $check = allRows('SELECT COUNT(*) n,SUM(valor_debito) db,SUM(valor_credito) cr,SUM(valor_saldo) saldo FROM contab_movimientos WHERE creado_por=?', [$plan['marca']])[0];
    ensure((int)$check['n'] === $expectedMovements && (int)$check['db'] === $totalPlan && (int)$check['cr'] === -$totalPlan && (int)$check['saldo'] === 0, 'Totales finales incorrectos.');
    ensure((int)allRows('SELECT COUNT(*) n FROM nom_contabilizaciones_procesos WHERE creado_por=?', [$plan['marca']])[0]['n'] === 5, 'Faltan procesos de nómina.');
    ensure((int)allRows('SELECT COUNT(*) n FROM contab_movimientos m LEFT JOIN contab_doc_registros r ON r.id=m.id_registro_doc_tipo_transaccion WHERE m.creado_por=? AND (r.id IS NULL OR r.contab_cuenta_id<>m.contab_cuenta_id OR r.core_tercero_id<>m.core_tercero_id OR r.valor_debito<>m.valor_debito OR r.valor_credito<>-m.valor_credito)', [$plan['marca']])[0]['n'] === 0, 'Movimientos sin detalle compatible.');
    sameRows(allRows('SELECT * FROM nom_consolidados_prestaciones_sociales ORDER BY id'), $snapshot['consolidados'], 'consolidados después de la carga');
    $output = ['estado'=>$mode === '--execute' ? 'COMMITTED' : 'DRY_RUN_ROLLBACK',
               'base'=>$plan['database'],'documentos'=>$result,'total_debito'=>$totalPlan,
               'total_credito'=>$totalPlan,'movimientos'=>$expectedMovements,
               'consolidados_mayo_sin_cambios'=>true,'plan_sha256'=>hash_file('sha256', $folder . '/plan.json')];
    if ($mode === '--execute') { $pdo->commit(); } else { $pdo->rollBack(); }
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} catch (Throwable $error) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
    exit(1);
}
