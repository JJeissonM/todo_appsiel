<?php

/**
 * Importa contratos docentes desde el Excel entregado por el colegio.
 *
 * Uso:
 *   php database/scripts/importar_contratos_docentes_2026_2027.php --dry-run
 *   php database/scripts/importar_contratos_docentes_2026_2027.php --backup
 *   php database/scripts/importar_contratos_docentes_2026_2027.php --execute
 *   php database/scripts/importar_contratos_docentes_2026_2027.php --verify
 */

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

$basePath = dirname(__DIR__, 2);
require $basePath . '/bootstrap/autoload.php';
$app = require $basePath . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$mode = isset($argv[1]) ? $argv[1] : '--dry-run';
if (!in_array($mode, ['--dry-run', '--backup', '--execute', '--verify'], true)) {
    fwrite(STDERR, "Modo invalido. Use --dry-run, --backup, --execute o --verify.\n");
    exit(2);
}

$expectedDatabase = 'u883985631_colegiobilingu';
$database = DB::connection()->getDatabaseName();
if ($database !== $expectedDatabase) {
    throw new RuntimeException("Base destino inesperada: {$database}");
}

$xlsx = $basePath . '/database/init/custom/Contratos_Nuevos 2026-2027_Docentes.xlsx';
if (!is_file($xlsx)) {
    throw new RuntimeException("No existe el archivo: {$xlsx}");
}

function normalized($value)
{
    $value = trim((string) $value);
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $ascii = $ascii === false ? $value : $ascii;
    return preg_replace('/[^A-Z0-9]+/', ' ', strtoupper($ascii));
}

function excelDateValue($value)
{
    if (is_numeric($value)) {
        return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
    }

    $value = trim((string) $value);
    foreach (['Y/m/d', 'Y-m-d', 'd/m/Y'] as $format) {
        $date = DateTime::createFromFormat('!' . $format, $value);
        if ($date && $date->format($format) === $value) {
            return $date->format('Y-m-d');
        }
    }
    throw new RuntimeException("Fecha invalida en Excel: {$value}");
}

function readRows($xlsx)
{
    $sheet = IOFactory::load($xlsx)->getActiveSheet();
    $headers = [];
    foreach ($sheet->getRowIterator(1, 1) as $row) {
        foreach ($row->getCellIterator() as $cell) {
            $headers[$cell->getColumn()] = normalized($cell->getValue());
        }
    }

    $expected = [
        'A' => 'TIPO', 'B' => 'DOCUMENTO DE IDENTIDAD', 'C' => 'PRIMER APELLIDO',
        'D' => 'SEGUNDO APELLIDO', 'E' => 'PRIMER NOMBRE', 'F' => 'SEGUNDO NOMBRE',
        'G' => 'CARGO', 'H' => 'CONTRATO', 'I' => 'TIPO COTIZANTE ID',
        'J' => 'SALARIO', 'K' => 'INICIO', 'L' => 'CULMINA', 'M' => 'EPS',
        'N' => 'FP', 'O' => 'FC',
    ];
    if ($headers !== $expected) {
        throw new RuntimeException('Las columnas del Excel no coinciden con el formato esperado.');
    }

    $rows = [];
    for ($number = 2; $number <= $sheet->getHighestDataRow(); $number++) {
        $document = preg_replace('/\D+/', '', (string) $sheet->getCell("B{$number}")->getValue());
        if ($document === '') {
            continue;
        }
        $rows[] = [
            'fila' => $number,
            'tipo_documento' => normalized($sheet->getCell("A{$number}")->getValue()),
            'documento' => $document,
            'apellido1' => trim((string) $sheet->getCell("C{$number}")->getValue()),
            'apellido2' => trim((string) $sheet->getCell("D{$number}")->getValue()),
            'nombre1' => trim((string) $sheet->getCell("E{$number}")->getValue()),
            'otros_nombres' => trim((string) $sheet->getCell("F{$number}")->getValue()),
            'cargo' => trim((string) $sheet->getCell("G{$number}")->getValue()),
            'clase_contrato' => strtolower(trim((string) $sheet->getCell("H{$number}")->getValue())),
            'tipo_cotizante' => trim((string) $sheet->getCell("I{$number}")->getValue()),
            'sueldo' => (float) $sheet->getCell("J{$number}")->getValue(),
            'fecha_ingreso' => excelDateValue($sheet->getCell("K{$number}")->getValue()),
            'contrato_hasta' => excelDateValue($sheet->getCell("L{$number}")->getValue()),
            'eps' => trim((string) $sheet->getCell("M{$number}")->getValue()),
            'pension' => trim((string) $sheet->getCell("N{$number}")->getValue()),
            'cesantias' => trim((string) $sheet->getCell("O{$number}")->getValue()),
        ];
    }
    return $rows;
}

function entityId($value, $field, $previousId)
{
    $key = normalized($value);
    $maps = [
        'eps' => [
            'SALUD TOTAL' => 3,
            'SANITAS' => 8,
            'NUEVA EPS' => 2,
            'DUSAKAWI' => 57,
            'CAJACOPI' => 58,
            // Proteger EPS es la nueva denominacion de Cajacopi EPS.
            'PROTEGER' => 58,
        ],
        'pension' => [
            'PORVENIR' => 36,
            'COLPENSIONES' => 55,
            'PROTECCION' => 35,
            'COLFONDOS' => 38,
        ],
        'cesantias' => [
            'PORVENIR' => 36,
            'PROTECCION' => 35,
            'COLFONDOS' => 38,
        ],
    ];

    foreach ($maps[$field] as $needle => $id) {
        if (strpos($key, $needle) !== false) {
            return $id;
        }
    }

    if ($key === 'NA' || $key === 'FNA') {
        // Las columnas son NOT NULL. La base historica usa el registro 59
        // (POR DEFINIR) para afiliaciones aun no parametrizadas.
        return 59;
    }

    // Nombres que no existen en el catalogo actual (p. ej. Coosalud,
    // Magisterio o Proteger) conservan la entidad previa del empleado.
    if ($previousId !== null) {
        return (int) $previousId;
    }

    throw new RuntimeException("No se pudo resolver {$field} '{$value}' sin contrato previo.");
}

function latestContract($thirdPartyId)
{
    return DB::table('nom_contratos')
        ->where('core_tercero_id', $thirdPartyId)
        ->orderByRaw("CASE WHEN estado = 'Activo' THEN 0 ELSE 1 END")
        ->orderBy('fecha_ingreso', 'desc')
        ->orderBy('id', 'desc')
        ->first();
}

function baseContract()
{
    return [
        'clase_contrato' => 'normal',
        'grupo_empleado_id' => 1,
        'clase_riesgo_laboral_id' => 1,
        'horas_laborales' => 210,
        'salario_integral' => 0,
        'entidad_arl_id' => 46,
        'liquida_subsidio_transporte' => 0,
        'planilla_pila_id' => 0,
        'es_pasante_sena' => 0,
        'entidad_caja_compensacion_id' => 71,
        'genera_planilla_integrada' => 0,
        'dias_laborados_mes' => 30,
        'excluir_documentos_nomina_electronica' => 0,
        'fingerprint_reader_id' => null,
        'turno_default_id' => null,
    ];
}

function prepareMigration(array $rows)
{
    $documents = array_column($rows, 'documento');
    if (count($documents) !== count(array_unique($documents))) {
        throw new RuntimeException('El Excel contiene documentos duplicados.');
    }

    $docTypes = ['CC' => 13, 'PT' => 45, 'PPT' => 45];
    $cargos = [];
    foreach (DB::table('nom_cargos')->where('estado', 'Activo')->get() as $cargo) {
        $cargos[normalized($cargo->descripcion)] = (int) $cargo->id;
    }

    $thirdPartiesByDocument = [];
    $thirdPartyIds = [];
    foreach (DB::table('core_terceros')->whereIn('numero_identificacion', $documents)->orderBy('id')->get() as $thirdParty) {
        $thirdPartiesByDocument[(string) $thirdParty->numero_identificacion][] = $thirdParty;
        $thirdPartyIds[] = (int) $thirdParty->id;
    }
    $contractsByThirdParty = [];
    if ($thirdPartyIds) {
        foreach (DB::table('nom_contratos')->whereIn('core_tercero_id', $thirdPartyIds)
            ->orderBy('fecha_ingreso', 'desc')->orderBy('id', 'desc')->get() as $contract) {
            $contractsByThirdParty[(int) $contract->core_tercero_id][] = $contract;
        }
    }

    $prepared = [];
    $summary = ['filas' => count($rows), 'terceros_nuevos' => 0, 'terceros_existentes' => 0,
        'contratos_activos_a_retirar' => 0, 'contratos_nuevos' => count($rows)];

    foreach ($rows as $row) {
        $thirdParties = isset($thirdPartiesByDocument[$row['documento']])
            ? $thirdPartiesByDocument[$row['documento']] : [];
        if (count($thirdParties) > 1) {
            throw new RuntimeException("Fila {$row['fila']}: documento {$row['documento']} tiene terceros duplicados.");
        }

        $thirdParty = count($thirdParties) === 1 ? $thirdParties[0] : null;
        $contracts = $thirdParty && isset($contractsByThirdParty[(int) $thirdParty->id])
            ? $contractsByThirdParty[(int) $thirdParty->id] : [];
        $latest = null;
        $activeIds = [];
        foreach ($contracts as $candidate) {
            if ($candidate->estado === 'Activo') {
                $activeIds[] = (int) $candidate->id;
                if ($latest === null) {
                    $latest = $candidate;
                }
            }
        }
        if ($latest === null && $contracts) {
            $latest = $contracts[0];
        }

        $docTypeKey = normalized($row['tipo_documento']);
        $cargoKey = normalized($row['cargo']);
        if (!isset($docTypes[$docTypeKey])) {
            throw new RuntimeException("Fila {$row['fila']}: tipo de documento desconocido {$row['tipo_documento']}.");
        }
        if (!isset($cargos[$cargoKey])) {
            throw new RuntimeException("Fila {$row['fila']}: cargo inexistente {$row['cargo']}.");
        }
        if ($row['sueldo'] <= 0 || $row['fecha_ingreso'] > $row['contrato_hasta']) {
            throw new RuntimeException("Fila {$row['fila']}: sueldo o fechas invalidos.");
        }

        $contract = $latest ? (array) $latest : baseContract();
        unset($contract['id'], $contract['created_at'], $contract['updated_at']);
        $contract = array_merge(baseContract(), $contract, [
            'core_tercero_id' => $thirdParty ? (int) $thirdParty->id : null,
            'clase_contrato' => $row['clase_contrato'],
            'cargo_id' => $cargos[$cargoKey],
            'horas_laborales' => 210,
            'sueldo' => $row['sueldo'],
            'fecha_ingreso' => $row['fecha_ingreso'],
            'contrato_hasta' => $row['contrato_hasta'],
            'entidad_salud_id' => entityId($row['eps'], 'eps', $latest ? $latest->entidad_salud_id : null),
            'entidad_pension_id' => entityId($row['pension'], 'pension', $latest ? $latest->entidad_pension_id : null),
            'entidad_cesantias_id' => entityId($row['cesantias'], 'cesantias', $latest ? $latest->entidad_cesantias_id : null),
            'estado' => 'Activo',
            'tipo_cotizante' => $row['tipo_cotizante'],
        ]);
        if (!$latest) {
            $contract['liquida_subsidio_transporte'] = $row['sueldo'] <= 3501810 ? 1 : 0;
        }

        $fullName = trim(implode(' ', array_filter([
            $row['nombre1'], $row['otros_nombres'], $row['apellido1'], $row['apellido2'],
        ], function ($value) { return $value !== ''; })));
        $newThirdParty = [
            'descripcion' => $fullName, 'core_empresa_id' => 1, 'imagen' => '',
            'tipo' => 'Persona natural', 'razon_social' => '', 'nombre1' => $row['nombre1'],
            'otros_nombres' => $row['otros_nombres'], 'apellido1' => $row['apellido1'],
            'apellido2' => $row['apellido2'], 'id_tipo_documento_id' => $docTypes[$docTypeKey],
            'numero_identificacion' => $row['documento'], 'digito_verificacion' => 0,
            'direccion1' => '', 'direccion2' => '', 'barrio' => '', 'codigo_ciudad' => 16920001,
            'codigo_postal' => 0, 'telefono1' => '', 'telefono2' => '', 'email' => '',
            'pagina_web' => '', 'estado' => 'Activo', 'user_id' => 0,
            'contab_anticipo_cta_id' => 0, 'contab_cartera_cta_id' => 0,
            'contab_cxp_cta_id' => 0, 'creado_por' => 'migracion_contratos_2026_2027',
            'modificado_por' => '',
        ];

        $summary[$thirdParty ? 'terceros_existentes' : 'terceros_nuevos']++;
        $summary['contratos_activos_a_retirar'] += count($activeIds);
        $prepared[] = compact('row', 'thirdParty', 'newThirdParty', 'activeIds', 'contract');
    }

    return [$prepared, $summary];
}

$rows = readRows($xlsx);
list($prepared, $summary) = prepareMigration($rows);

if ($mode === '--verify') {
    $verified = 0;
    $contractIds = [];
    foreach ($prepared as $item) {
        if (!$item['thirdParty'] || count($item['activeIds']) !== 1) {
            throw new RuntimeException("Fila {$item['row']['fila']}: no tiene exactamente un contrato activo.");
        }
        $actual = DB::table('nom_contratos')->where('id', $item['activeIds'][0])->first();
        $expected = $item['contract'];
        foreach ([
            'core_tercero_id', 'clase_contrato', 'cargo_id', 'horas_laborales', 'sueldo',
            'fecha_ingreso', 'contrato_hasta', 'entidad_salud_id', 'entidad_pension_id',
            'entidad_cesantias_id', 'estado', 'tipo_cotizante',
        ] as $field) {
            if ((string) $actual->{$field} !== (string) $expected[$field]) {
                throw new RuntimeException("Fila {$item['row']['fila']}: diferencia en {$field}.");
            }
        }
        $contractIds[] = (int) $actual->id;
        $verified++;
    }
    sort($contractIds);
    echo json_encode([
        'mode' => 'verify', 'database' => $database, 'verified_rows' => $verified,
        'active_contracts' => count($contractIds), 'first_contract_id' => reset($contractIds),
        'last_contract_id' => end($contractIds),
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

if ($mode === '--backup') {
    $backup = ['generated_at' => date(DATE_ATOM), 'database' => $database, 'summary' => $summary, 'records' => []];
    foreach ($prepared as $item) {
        $backup['records'][] = [
            'excel' => $item['row'],
            'tercero' => $item['thirdParty'],
            'contratos' => $item['thirdParty'] ? DB::table('nom_contratos')
                ->where('core_tercero_id', $item['thirdParty']->id)->orderBy('id')->get() : [],
        ];
    }
    echo json_encode($backup, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

if ($mode === '--dry-run') {
    echo json_encode(['mode' => 'dry-run', 'database' => $database, 'summary' => $summary], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
    exit(0);
}

$result = DB::transaction(function () use ($prepared) {
    $createdThirdParties = [];
    $retiredContracts = [];
    $createdContracts = [];
    foreach ($prepared as $item) {
        $thirdPartyId = $item['thirdParty'] ? (int) $item['thirdParty']->id : null;
        if ($thirdPartyId === null) {
            $thirdPartyData = $item['newThirdParty'];
            $thirdPartyData['created_at'] = date('Y-m-d H:i:s');
            $thirdPartyData['updated_at'] = date('Y-m-d H:i:s');
            $thirdPartyId = DB::table('core_terceros')->insertGetId($thirdPartyData);
            $createdThirdParties[] = $thirdPartyId;
        }

        if ($item['activeIds']) {
            DB::table('nom_contratos')->whereIn('id', $item['activeIds'])->update([
                'estado' => 'Retirado',
                'contrato_hasta' => date('Y-m-d', strtotime($item['row']['fecha_ingreso'] . ' -1 day')),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $retiredContracts = array_merge($retiredContracts, array_map('intval', $item['activeIds']));
        }

        $contract = $item['contract'];
        $contract['core_tercero_id'] = $thirdPartyId;
        $contract['created_at'] = date('Y-m-d H:i:s');
        $contract['updated_at'] = date('Y-m-d H:i:s');
        $createdContracts[] = DB::table('nom_contratos')->insertGetId($contract);
    }
    return compact('createdThirdParties', 'retiredContracts', 'createdContracts');
});

// Verificacion posterior fuera de la transaccion.
$activeCount = DB::table('nom_contratos')
    ->whereIn('id', $result['createdContracts'])->where('estado', 'Activo')->count();
if ($activeCount !== count($prepared)) {
    throw new RuntimeException('La verificacion posterior no encontro todos los contratos activos creados.');
}

echo json_encode([
    'mode' => 'execute', 'database' => $database, 'summary' => $summary,
    'created_third_party_ids' => $result['createdThirdParties'],
    'retired_contract_ids' => $result['retiredContracts'],
    'created_contract_ids' => $result['createdContracts'],
    'verified_active_contracts' => $activeCount,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;
