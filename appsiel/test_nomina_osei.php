<?php
/**
 * TEST DE NÓMINA ELECTRÓNICA - Appsiel → OSEI
 * 
 * Uso desde terminal:
 *   php test_nomina_osei.php                    # TESTING (simulación)
 *   php test_nomina_osei.php pruebas            # PRUEBAS (envío real a DIAN)
 *   php test_nomina_osei.php produccion         # PRODUCCION (envío real a DIAN)
 * 
 * Funciona en Windows y Linux.
 */

$token = 'ed5b78db8a6c7d07ede7af5f1312efe0a7a583a4debb1fad479f025b940d98a3';
$nit = '901181352';

// ── Elegir ambiente ──
$ambiente = $argv[1] ?? 'TESTING';
switch (strtoupper($ambiente)) {
    case 'PRUEBAS':
        $ambienteOsei = 'DIAN_TEST';
        $host = 'https://testing.osei.com.co';
        $label = 'PRUEBAS (envío real a DIAN)';
        break;
    case 'PRODUCCION':
        $ambienteOsei = 'DIAN_PRODUCTION';
        $host = 'https://osei.com.co';
        $label = 'PRODUCCION (envío real a DIAN)';
        break;
    default:
        $ambienteOsei = 'TESTING';
        $host = 'https://testing.osei.com.co';
        $label = 'TESTING (simulación, sin DIAN)';
        break;
}

$url = $host . '/api/v1/electronic-payroll/documents';
$numero = (int)date('His');
$fecha = date('Y-m-d');
$hora = date('H:i:s') . '-05:00';
$extDocId = $ambienteOsei . '-COMPLETO-' . date('YmdHis');

echo "╔══════════════════════════════════════════╗\n";
echo "║     TEST NÓMINA ELECTRÓNICA → OSEI      ║\n";
echo "╚══════════════════════════════════════════╝\n\n";
echo "🌐 Ambiente: {$label}\n";
echo "📍 URL: {$url}\n";
echo "📄 Documento: NE{$numero}\n";
echo "🆔 External ID: {$extDocId}\n\n";

// ── JSON COMPLETO con devengados + deducciones ──
$payload = [
    "authorizationToken" => $token,
    "environment" => $ambienteOsei,
    "documentType" => "PAYROLL_SUPPORT_DOCUMENT",
    "operationType" => "ISSUE",
    "externalDocumentId" => $extDocId,

    "period" => ["year" => 2026, "month" => 7],
    "settlementPeriod" => [
        "startDate" => "2026-07-01",
        "endDate" => "2026-07-31"
    ],
    "issue" => [
        "issueDate" => $fecha,
        "issueTime" => $hora
    ],
    "sequence" => [
        "prefix" => "NE",
        "number" => $numero
    ],

    "employer" => [
        "identificationType" => "NIT",
        "identificationNumber" => $nit,
        "verificationDigit" => "0",
        "businessName" => "EMPRESA DE PRUEBA S.A.S.",
        "municipalityCode" => "001",
        "departmentCode" => "20",
        "countryCode" => "CO",
        "address" => "Cra 45 # 20-30"
    ],

    "employee" => [
        "identificationType" => "CC",
        "identificationNumber" => "1065000000",
        "firstName" => "Carlos",
        "middleName" => "Andres",
        "lastName" => "Perez",
        "secondLastName" => "Lopez",
        "workerType" => "DEPENDENT",
        "contractType" => "FIXED_TERM",
        "salaryType" => "FIXED",
        "municipalityCode" => "001",
        "departmentCode" => "20",
        "countryCode" => "CO",
        "address" => "Calle 10 # 5-40",
        "highRiskPension" => false,
        "integralSalary" => false
    ],

    "payment" => [
        "paymentMethod" => "BANK_TRANSFER"
    ],

    "earnings" => [
        "basicSalary" => ["workedDays" => 30, "amount" => 1800000.00],
        "transportationAllowance" => 200000.00,
        "overtime" => [[
            "code" => "HORA_EXTRA_DIURNA",
            "type" => "DAYTIME_OVERTIME",
            "quantity" => 4,
            "unit" => "HOUR",
            "percentage" => 25,
            "paymentAmount" => 62500.00
        ]],
        "bonuses" => [["type" => "BONIFICACION", "paymentAmount" => 100000.00]],
        "commissions" => [],
        "severance" => ["paymentAmount" => 0, "interestPaymentAmount" => 0],
        "vacations" => [],
        "incapacities" => [],
        "licenses" => [],
        "otherEarnings" => []
    ],

    "deductions" => [
        "health" => ["percentage" => 4, "amount" => 72000.00],
        "pension" => ["percentage" => 4, "amount" => 72000.00],
        "solidarityPensionFund" => ["percentage" => 0, "amount" => 0],
        "withholdingTax" => 50000.00,
        "loans" => [[
            "code" => "LIBRANZA",
            "type" => "LOAN",
            "paymentAmount" => 150000.00
        ]],
        "advances" => [],
        "unionDues" => [],
        "sanctions" => [],
        "otherDeductions" => []
    ],

    "totals" => [
        "totalEarnings" => 2162500.00,
        "totalDeductions" => 344000.00,
        "netPay" => 1818500.00
    ]
];

// ── Enviar a OSEI ──
echo "╔══════════════════════════════════════════╗\n";
echo "║         ENVIANDO A OSEI...               ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 60,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "═══════════════════════════════════════════\n";
echo "  HTTP {$httpCode}\n";
echo "═══════════════════════════════════════════\n\n";

if ($error) {
    echo "❌ Error de conexión: {$error}\n\n";
    exit(1);
}

$decoded = json_decode($response, true);
if ($decoded) {
    echo json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n";
} else {
    echo $response . "\n\n";
}

// ── Verificar resultado ──
$success = $decoded['success'] ?? false;
if ($success) {
    echo "✅  DOCUMENTO ENVIADO EXITOSAMENTE\n";
    echo "   ID en OSEI: " . ($decoded['documentId'] ?? '?') . "\n";
    echo "   Estado: " . ($decoded['status'] ?? '?') . "\n";
    if (!empty($decoded['cune'])) {
        echo "   CUNE: " . $decoded['cune'] . "\n";
    } else {
        echo "   (sin CUNE en modo TESTING)\n";
    }
} elseif ($httpCode === 401) {
    echo "❌  TOKEN INVÁLIDO. Verificar tokenEmpresa en config/facturacion_electronica.php\n";
} else {
    echo "⚠️  REVISAR ERRORES ARRIBA\n";
}
echo "\n";