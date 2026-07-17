<?php

namespace App\NominaElectronica\Services;

use App\Core\Empresa;
use App\Core\Services\CompanyService;
use App\NominaElectronica\DATAICO\DocumentoSoporte;
use App\NominaElectronica\ResultadoEnvioDocumento;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class OseiPayrollService
{
    const OSEI_ENDPOINT = '/api/v1/electronic-payroll/documents';
    const OSEI_ENDPOINT_AJUSTE = '/api/v1/electronic-payroll/adjustment-notes';

    protected $companyService;
    protected $empresa;
    protected $authorizationToken;

    public function __construct()
    {
        $this->companyService = new CompanyService();
        $this->empresa = $this->companyService->company;
        // Token de autenticación para OSEI
        // Se toma de config('nomina.tokenPassword') que se configura desde la vista de parámetros
        $this->authorizationToken = config('nomina.tokenPassword');
    }

    /**
     * Envía un documento de nómina electrónica a OSEI
     */
    public function enviarDocumento(DocumentoSoporte $documento): array
    {
        try {
            $jsonPayload = $this->construirJsonOsei($documento);
            $jsonPayload['authorizationToken'] = $this->authorizationToken;

            $oseiUrl = rtrim(config('nomina.url_servicio_emision'), '/') . self::OSEI_ENDPOINT;

            $response = $this->enviarPeticion($oseiUrl, $jsonPayload);

            return $this->procesarRespuesta($response, $documento, $jsonPayload);
        } catch (\Throwable $e) {
            Log::error('OSEl Payroll: Error enviando documento a OSEI', [
                'documento_id' => $documento->id,
                'consecutivo' => $documento->consecutivo,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'documento_id' => $documento->id,
                'message' => 'Error de conexión con OSEI: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Construye el JSON en el formato que espera OSEI
     */
    public function construirJsonOsei(DocumentoSoporte $documento): array
    {
        $empleado = $documento->empleado;
        $tercero = $empleado->tercero;

        if (is_null($tercero)) {
            throw new \RuntimeException('El contrato #' . $empleado->id . ' no tiene tercero asociado.');
        }

        $headData = json_decode($documento->head_data_json, true) ?? [];
        $accruals = json_decode($documento->accruals_json, true) ?? [];
        $deductions = json_decode($documento->deductions_json, true) ?? [];

        // ── Periodo y fechas ──
        $fechaFinal = $documento->fecha;
        list($anio, $mes) = explode('-', $fechaFinal);
        $fechaInicial = $anio . '-' . $mes . '-01';

        // Fecha de emisión (hoy) con timezone Colombia
        $hoy = Carbon::now();
        $issueDate = $hoy->format('Y-m-d');
        $issueTime = $hoy->format('H:i:s') . '-05:00';

        // ── Empleador (empresa) ──
        $ciudadEmpleador = $this->empresa->ciudad;
        $codigoDepartamento = '';
        $codigoMunicipio = '';
        if ($ciudadEmpleador) {
            $idCiudad = (string) $ciudadEmpleador->id;
            $codigoDepartamento = substr($idCiudad, 3, 2);
            $codigoMunicipio = substr($idCiudad, 5);
        }

        $tipoDocEmpresa = $this->empresa->tipo_doc_identidad;
        $tipoIdentificacionEmpleador = $tipoDocEmpresa ? $tipoDocEmpresa->abreviatura : 'NIT';

        $digitoVerificacion = $this->empresa->digito_verificacion;

        // ── Empleado ──
        $tipoDocEmpleado = $tercero->tipo_doc_identidad;
        $tipoIdentificacionEmpleado = $this->mapearTipoIdentificacion($tipoDocEmpleado ? $tipoDocEmpleado->abreviatura : 'CC');

        $primerNombre = $tercero->nombre1 ?? '';
        $otrosNombres = $tercero->otros_nombres ?? '';
        $primerApellido = $tercero->apellido1 ?? '';
        $segundoApellido = $tercero->apellido2 ?? '';

        $ciudadEmpleado = $tercero->ciudad;
        $codDeptoEmpleado = '';
        $codMunEmpleado = '';
        if ($ciudadEmpleado) {
            $idCiudadEmp = (string) $ciudadEmpleado->id;
            $codDeptoEmpleado = substr($idCiudadEmp, 3, 2);
            $codMunEmpleado = substr($idCiudadEmp, 5);
        }

        // ── Procesar devengados ──
        $earnings = $this->procesarDevengados($accruals);

        // ── Procesar deducciones ──
        $deduccionesProcesadas = $this->procesarDeducciones($deductions);

        // ── Calcular totales ──
        $totalEarnings = $earnings['totalEarnings'];
        $totalDeductions = $deduccionesProcesadas['totalDeductions'];
        $netPay = round($totalEarnings - $totalDeductions, 2);

        // Obtener prefijo: primero de head_data_json, luego del tipo de documento, luego default
        $prefix = $headData['prefix'] ?? '';
        if ($prefix === '' && !is_null($documento->tipo_documento_app)) {
            $prefix = $documento->tipo_documento_app->prefijo;
        }
        if ($prefix === '') {
            $prefix = 'NE';
        }
        // Si el prefix viene con espacios como "N 1", extraer solo el prefijo
        if (strpos($prefix, ' ') !== false) {
            $parts = explode(' ', $prefix);
            $prefix = $parts[0];
        }

        return [
            'environment' => $this->mapearAmbiente(config('nomina.nom_elec_ambiente')),
            'documentType' => 'PAYROLL_SUPPORT_DOCUMENT',
            'operationType' => 'ISSUE',
            'externalDocumentId' => $documento->core_tipo_doc_app_id . '-' . $documento->consecutivo . '-' . $tercero->numero_identificacion,

            'period' => [
                'year' => (int)$anio,
                'month' => (int)$mes,
            ],
            'settlementPeriod' => [
                'startDate' => $fechaInicial,
                'endDate' => $fechaFinal,
            ],
            'issue' => [
                'issueDate' => $issueDate,
                'issueTime' => $issueTime,
            ],
            'sequence' => [
                'prefix' => $prefix,
                'number' => (int)$documento->consecutivo,
            ],

            'employer' => [
                'identificationType' => $tipoIdentificacionEmpleador,
                'identificationNumber' => (string) $this->empresa->numero_identificacion,
                'verificationDigit' => $digitoVerificacion,
                'businessName' => $this->empresa->descripcion,
                'municipalityCode' => $codigoMunicipio,
                'departmentCode' => $codigoDepartamento,
                'countryCode' => 'CO',
                'address' => $this->empresa->direccion1 ?? '',
            ],

            'employee' => [
                'identificationType' => $tipoIdentificacionEmpleado,
                'identificationNumber' => (string) $tercero->numero_identificacion,
                'firstName' => $primerNombre ?: 'SIN',
                'middleName' => $otrosNombres,
                'lastName' => $primerApellido ?: 'NOMBRE',
                'secondLastName' => $segundoApellido,
                'workerType' => $this->mapearTipoTrabajador($empleado->tipo_cotizante),
                'contractType' => $this->mapearTipoContrato($empleado->contrato_hasta),
                'salaryType' => $empleado->salario_integral ? 'INTEGRAL' : 'FIXED',
                'municipalityCode' => $codMunEmpleado,
                'departmentCode' => $codDeptoEmpleado,
                'countryCode' => 'CO',
                'address' => $tercero->direccion1 ?? '',
                'highRiskPension' => false,
                'integralSalary' => (bool)$empleado->salario_integral,
            ],

            'payment' => [
                'paymentMethod' => 'BANK_TRANSFER',
            ],

            'earnings' => $earnings['data'],
            'deductions' => $deduccionesProcesadas['data'],
            'totals' => [
                'totalEarnings' => $totalEarnings,
                'totalDeductions' => $totalDeductions,
                'netPay' => $netPay,
            ],
        ];
    }

    /**
     * Procesa los devengados de Appsiel al formato OSEI
     */
    protected function procesarDevengados(array $accruals): array
    {
        $earnings = [
            'basicSalary' => ['workedDays' => 0, 'amount' => 0],
            'transportationAllowance' => 0,
            'overtime' => [],
            'surcharges' => [],
            'bonuses' => [],
            'commissions' => [],
            'severance' => ['paymentAmount' => 0, 'interestPaymentAmount' => 0],
            'vacations' => [],
            'incapacities' => [],
            'licenses' => [],
            'serviceBonus' => ['quantity' => 0, 'paymentAmount' => 0],
            'otherEarnings' => [],
        ];

        $totalEarnings = 0;

        foreach ($accruals as $line) {
            $amount = (float)($line['amount'] ?? 0);
            $code = $line['code'] ?? '';
            $days = (int)($line['days'] ?? 0);
            $hours = (int)($line['hours'] ?? 0);
            $percentage = (float)($line['percentage'] ?? 0);

            switch ($code) {
                case 'BASICO':
                    $earnings['basicSalary']['workedDays'] = $days ?: 30;
                    $earnings['basicSalary']['amount'] = $amount;
                    break;

                case 'AUXILIO_DE_TRANSPORTE':
                    $earnings['transportationAllowance'] = $amount;
                    break;

                case 'HORA_EXTRA_DIURNA':
                case 'HORA_EXTRA_NOCTURNA':
                case 'HORA_EXTRA_DIURNA_DF':
                case 'HORA_EXTRA_NOCTURNA_DF':
                case 'HORA_RECARGO_NOCTURNO':
                case 'HORA_RECARGO_DIURNA_DF':
                case 'HORA_RECARGO_NOCTURNO_DF':
                    $earnings['overtime'][] = [
                        'code' => $code,
                        'type' => $this->mapearTipoHoraExtra($code),
                        'quantity' => $hours,
                        'unit' => 'HOUR',
                        'percentage' => $percentage ?: null,
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'VACACION':
                case 'VACACION_COMPENSADA':
                    $earnings['vacations'][] = [
                        'type' => $code,
                        'quantity' => $days,
                        'unit' => 'DAY',
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'PRIMA':
                case 'BONIFICACION':
                case 'BONIFICACION_RETIRO':
                    $earnings['bonuses'][] = [
                        'type' => $code,
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'COMISION':
                    $earnings['commissions'][] = [
                        'type' => $code,
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'CESANTIAS':
                    $interestAmount = (float)($line['cesantias-interest'] ?? 0);
                    $earnings['severance']['paymentAmount'] = $amount;
                    $earnings['severance']['interestPaymentAmount'] = $interestAmount;
                    break;

                case 'INCAPACIDAD':
                    $earnings['incapacities'][] = [
                        'type' => 'COMMON_ILLNESS',
                        'quantity' => $days,
                        'unit' => 'DAY',
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'LICENCIA_REMUNERADA':
                case 'LICENCIA_NO_REMUNERADA':
                case 'LICENCIA_PATERNIDAD':
                    $earnings['licenses'][] = [
                        'type' => $code,
                        'quantity' => $days,
                        'unit' => 'DAY',
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'COMPENSACION':
                    $earnings['serviceBonus']['quantity'] = $days;
                    $earnings['serviceBonus']['paymentAmount'] = $amount;
                    break;

                default:
                    // OTRO_CONCEPTO, AUXILIO u otros no categorizados
                    if ($code === 'AUXILIO') {
                        $earnings['transportationAllowance'] += $amount;
                    } else {
                        $earnings['otherEarnings'][] = [
                            'code' => $code,
                            'type' => 'OTHER_EARNING',
                            'quantity' => $days ?: 0,
                            'paymentAmount' => $amount,
                        ];
                    }
                    break;
            }

            $totalEarnings += $amount;

            // Manejar cesantías interest cuando vienen en línea separada
            if (isset($line['cesantias-interest']) && (float)$line['cesantias-interest'] > 0 && $code !== 'CESANTIAS') {
                // El interés viene como concepto separado con modo_liquidacion_id=16
                // No lo duplicamos porque ya se manejó arriba
            }
        }

        // Si no hay básico, agregarlo con 0
        if ($earnings['basicSalary']['amount'] == 0) {
            $earnings['basicSalary']['workedDays'] = 30;
            $earnings['basicSalary']['amount'] = 0;
        }

        return [
            'data' => $earnings,
            'totalEarnings' => $totalEarnings,
        ];
    }

    /**
     * Procesa las deducciones de Appsiel al formato OSEI
     */
    protected function procesarDeducciones(array $deductions): array
    {
        $ded = [
            'health' => ['percentage' => 0, 'amount' => 0],
            'pension' => ['percentage' => 0, 'amount' => 0],
            'solidarityPensionFund' => ['percentage' => 0, 'amount' => 0],
            'withholdingTax' => 0,
            'loans' => [],
            'advances' => [],
            'unionDues' => [],
            'sanctions' => [],
            'otherDeductions' => [],
        ];

        $totalDeductions = 0;

        foreach ($deductions as $line) {
            $amount = (float)($line['amount'] ?? 0);
            $code = $line['code'] ?? '';
            $percentage = (float)($line['percentage'] ?? 0);

            switch ($code) {
                case 'SALUD':
                    $ded['health']['percentage'] = $percentage ?: 4;
                    $ded['health']['amount'] = $amount;
                    break;

                case 'FONDO_PENSION':
                    $ded['pension']['percentage'] = $percentage ?: 4;
                    $ded['pension']['amount'] = $amount;
                    break;

                case 'FONDO_SOLIDARIDAD_PENSIONAL':
                    $ded['solidarityPensionFund']['percentage'] = $percentage;
                    $ded['solidarityPensionFund']['amount'] = $amount;
                    break;

                case 'RETENCION_FUENTE':
                    $ded['withholdingTax'] = $amount;
                    break;

                case 'LIBRANZA':
                case 'COOPERATIVA':
                case 'DEUDA':
                case 'PAGO_TERCERO':
                    $ded['loans'][] = [
                        'code' => $code,
                        'type' => 'LOAN',
                        'paymentAmount' => $amount,
                    ];
                    break;

                case 'ANTICIPO':
                    $ded['advances'][] = [
                        'amount' => $amount,
                    ];
                    break;

                case 'SINDICATO':
                    $ded['unionDues'][] = [
                        'amount' => $amount,
                    ];
                    break;

                case 'SANCION':
                    $ded['sanctions'][] = [
                        'amount' => $amount,
                    ];
                    break;

                case 'AFC':
                case 'PLANES_COMPLEMENTARIOS':
                case 'EDUCACION':
                case 'PENSION_VOLUNTARIA':
                case 'EMBARGO_FISCAL':
                case 'FONDO_SUBSISTENCIA':
                    $ded['otherDeductions'][] = [
                        'code' => $code,
                        'type' => 'OTHER_DEDUCTION',
                        'paymentAmount' => $amount,
                    ];
                    break;

                default:
                    $ded['otherDeductions'][] = [
                        'code' => $code,
                        'type' => 'OTHER_DEDUCTION',
                        'paymentAmount' => $amount,
                    ];
                    break;
            }

            $totalDeductions += $amount;
        }

        return [
            'data' => $ded,
            'totalDeductions' => $totalDeductions,
        ];
    }

    /**
      * Envía la petición HTTP a OSEI
      */
    protected function enviarPeticion(string $url, array $payload): ?\Psr\Http\Message\ResponseInterface
    {
        $config = [
            'timeout' => 120,
            'connect_timeout' => 30,
            'http_errors' => false,
        ];

        // Soporte para proxy HTTP/HTTPS desde configuración
        $proxyUrl = config('nomina.proxy_url');
        if (!empty($proxyUrl)) {
            $config['proxy'] = $proxyUrl;
        }

        $client = new Client($config);

        return $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $payload,
        ]);
    }

    /**
     * Procesa la respuesta de OSEI y la guarda
     */
    protected function procesarRespuesta(?\Psr\Http\Message\ResponseInterface $response, DocumentoSoporte $documento, array $jsonEnviado): array
    {
        if (is_null($response)) {
            $this->guardarResultado($documento, [
                'codigo' => 0,
                'dian_status' => 'DIAN_RECHAZADO',
                'dian_messages' => 'No hubo respuesta del servidor OSEI.',
            ], $jsonEnviado);

            return [
                'ok' => false,
                'documento_id' => $documento->id,
                'message' => 'No hubo respuesta del servidor OSEI.',
            ];
        }

        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();
        $respuestaJson = json_decode($body, true) ?? [];

        $exitoso = ($respuestaJson['success'] ?? false) === true;
        $dianStatus = $exitoso ? 'DIAN_ACEPTADO' : 'DIAN_RECHAZADO';
        $documentoId = $respuestaJson['documentId'] ?? null;
        $statusDoc = $respuestaJson['status'] ?? null;
        $cune = $respuestaJson['cune'] ?? null;
        $errores = $respuestaJson['errors'] ?? ($respuestaJson['message'] ?? 'Error desconocido');
        $esSimulacion = ($respuestaJson['simulation'] ?? false) === true;

        $this->guardarResultado($documento, [
            'codigo' => $statusCode,
            'dian_status' => $dianStatus,
            'dian_messages' => is_array($errores) ? json_encode($errores) : (string)$errores,
            'cune' => $cune,
            'number' => $documentoId,
        ], $jsonEnviado);

        if ($exitoso) {
            $documento->estado = 'Enviado';
            $documento->save();

            $mensaje = 'Documento enviado correctamente a OSEI.';
            if ($esSimulacion) {
                $mensaje .= ' (Modo TESTING)';
            }

            return [
                'ok' => true,
                'documento_id' => $documento->id,
                'documentoIdOsei' => $documentoId,
                'status' => $statusDoc,
                'cune' => $cune,
                'message' => $mensaje,
            ];
        }

        $mensajesError = [];
        if (is_array($errores)) {
            foreach ($errores as $error) {
                if (is_array($error)) {
                    $mensajesError[] = ($error['field'] ?? '') . ': ' . ($error['message'] ?? json_encode($error));
                } else {
                    $mensajesError[] = (string)$error;
                }
            }
        } else {
            $mensajesError[] = (string)$errores;
        }

        return [
            'ok' => false,
            'documento_id' => $documento->id,
            'statusCode' => $statusCode,
            'message' => empty($mensajesError) ? 'El documento fue rechazado por OSEI.' : implode(' | ', $mensajesError),
        ];
    }

    /**
     * Guarda el resultado del envío en la tabla de resultados
     */
    protected function guardarResultado(DocumentoSoporte $documento, array $data, array $jsonEnviado): void
    {
        $registro = [
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo,
            'fecha' => $documento->fecha,
            'codigo' => $data['codigo'] ?? 0,
            'dian_status' => $data['dian_status'] ?? 'DIAN_RECHAZADO',
            'dian_messages' => $data['dian_messages'] ?? null,
            'cune' => $data['cune'] ?? '',
            'number' => $data['number'] ?? $documento->consecutivo,
            'objeto_json_enviado' => json_encode($jsonEnviado),
        ];

        ResultadoEnvioDocumento::create($registro);
    }

    // ── Mapeo de tipo de identificación ──

    protected function mapearTipoIdentificacion(?string $tipoOriginal): string
    {
        // OSEI solo acepta: CC, NIT, CE, TI, PA, PE, RC
        $map = [
            'DNI' => 'CC',
            'NIT' => 'NIT',
            'CC' => 'CC',
            'CE' => 'CE',
            'TI' => 'TI',
            'PA' => 'PA',
            'PE' => 'PE',
            'RC' => 'RC',
        ];
        return $map[$tipoOriginal] ?? 'CC';
    }

    // ── Mapeo de ambientes ──

    protected function mapearAmbiente(?string $ambienteAppsiel): string
    {
        switch ($ambienteAppsiel) {
            case 'TESTING':
                return 'TESTING';
            case 'PRUEBAS':
                return 'DIAN_TEST';
            case 'PRODUCCION':
                return 'DIAN_PRODUCTION';
            default:
                return 'TESTING';
        }
    }

    // ── Mapeo de tipos de trabajador ──

    protected function mapearTipoTrabajador(?string $tipoCotizante): string
    {
        switch ($tipoCotizante) {
            case '01': return 'DEPENDENT';
            case '12': return 'APPRENTICE';
            case '19': return 'APPRENTICE';
            case '22': return 'TEACHER';
            case '51': return 'PART_TIME';
            default:   return 'DEPENDENT';
        }
    }

    // ── Mapeo de tipos de contrato ──

    protected function mapearTipoContrato($contratoHasta): string
    {
        if (is_null($contratoHasta) || $contratoHasta === '' || $contratoHasta === '2099-12-30') {
            return 'INDEFINITE_TERM';
        }
        return 'FIXED_TERM';
    }

    // ── Mapeo de horas extra ──

    protected function mapearTipoHoraExtra(string $codigoDian): string
    {
        $map = [
            'HORA_EXTRA_DIURNA' => 'DAYTIME_OVERTIME',
            'HORA_EXTRA_NOCTURNA' => 'NIGHT_OVERTIME',
            'HORA_EXTRA_DIURNA_DF' => 'SUNDAY_DAYTIME_OVERTIME',
            'HORA_EXTRA_NOCTURNA_DF' => 'SUNDAY_NIGHT_OVERTIME',
            'HORA_RECARGO_NOCTURNO' => 'NIGHT_OVERTIME',
            'HORA_RECARGO_DIURNA_DF' => 'HOLIDAY_DAYTIME_OVERTIME',
            'HORA_RECARGO_NOCTURNO_DF' => 'HOLIDAY_NIGHT_OVERTIME',
        ];

        return $map[$codigoDian] ?? 'DAYTIME_OVERTIME';
    }
}