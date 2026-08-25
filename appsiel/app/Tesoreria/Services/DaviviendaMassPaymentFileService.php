<?php

namespace App\Tesoreria\Services;

use App\Compras\ProveedorCuentaBancaria;
use App\Core\Empresa;
use App\CxP\CxpAbono;
use App\Nomina\NomPagoAutomatico;
use App\Tesoreria\TesoDocEncabezado;
use Illuminate\Support\Facades\Schema;

class DaviviendaMassPaymentFileService
{
    const BANK_CODE = 51;
    const RECORD_LENGTH = 170;

    public function summary(TesoDocEncabezado $payment)
    {
        $result = $this->collectBeneficiaries($payment);

        return [
            'available' => $this->isDaviviendaBankPayment($payment) && count($result['eligible']) > 0,
            'is_davivienda_bank_payment' => $this->isDaviviendaBankPayment($payment),
            'eligible_count' => count($result['eligible']),
            'omitted_count' => count($result['omitted']),
            'omitted' => $result['omitted'],
            'total' => $this->centsToDecimal($this->sumCents($result['eligible']))
        ];
    }

    public function generate(TesoDocEncabezado $payment)
    {
        $this->validatePayment($payment);
        $result = $this->collectBeneficiaries($payment);

        if (empty($result['eligible'])) {
            throw new \InvalidArgumentException('El pago no tiene beneficiarios con cuentas bancarias activas y válidas.');
        }

        $company = Empresa::find($payment->core_empresa_id);
        if (is_null($company)) {
            throw new \InvalidArgumentException('No se encontró la empresa del documento de pago.');
        }

        $now = new \DateTime('now');
        $serviceCode = $this->isPayrollPayment($payment) ? 'NOMI' : 'PROV';
        $totalCents = $this->sumCents($result['eligible']);
        $lines = [
            $this->controlRecord($payment, $company, $serviceCode, $totalCents, count($result['eligible']), $now)
        ];

        foreach ($result['eligible'] as $beneficiary) {
            $lines[] = $this->transferRecord($beneficiary);
        }

        foreach ($lines as $line) {
            if (strlen($line) !== self::RECORD_LENGTH) {
                throw new \RuntimeException('El archivo no pudo generarse porque uno de sus registros no tiene 170 caracteres.');
            }
        }

        $content = implode("\r\n", $lines) . "\r\n";
        $fileName = $serviceCode . '_DAVIVIENDA_' . $now->format('Ymd_His') . '_PAGO_' . $payment->id . '.TXT';

        return [
            'content' => $content,
            'file_name' => $fileName,
            'hash' => hash('sha256', $content),
            'count' => count($result['eligible']),
            'omitted_count' => count($result['omitted']),
            'omitted' => $result['omitted'],
            'total' => $this->centsToDecimal($totalCents),
            'service_code' => $serviceCode
        ];
    }

    protected function validatePayment(TesoDocEncabezado $payment)
    {
        if ((int) $payment->core_tipo_transaccion_id !== 33) {
            throw new \InvalidArgumentException('El documento no es un Pago de CxP.');
        }
        if ($payment->estado === 'Anulado') {
            throw new \InvalidArgumentException('No se puede generar un archivo para un pago anulado.');
        }
        if (!$this->isDaviviendaBankPayment($payment)) {
            throw new \InvalidArgumentException('El pago debe haberse generado desde una cuenta bancaria Davivienda activa.');
        }
    }

    protected function isDaviviendaBankPayment(TesoDocEncabezado $payment)
    {
        $account = $payment->cuenta_bancaria;

        return !is_null($account)
            && $account->estado === 'Activo'
            && (int) $account->entidad_financiera_id === self::BANK_CODE;
    }

    protected function isPayrollPayment(TesoDocEncabezado $payment)
    {
        return Schema::hasTable('nom_pagos_automaticos')
            && NomPagoAutomatico::where('teso_doc_encabezado_id', $payment->id)->exists();
    }

    protected function collectBeneficiaries(TesoDocEncabezado $payment)
    {
        $payments = CxpAbono::with('tercero.tipo_doc_identidad')
            ->where('core_empresa_id', $payment->core_empresa_id)
            ->where('core_tipo_transaccion_id', $payment->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $payment->core_tipo_doc_app_id)
            ->where('consecutivo', $payment->consecutivo)
            ->get()
            ->groupBy('core_tercero_id');

        $eligible = [];
        $omitted = [];

        foreach ($payments as $thirdPartyId => $thirdPartyPayments) {
            $thirdParty = $thirdPartyPayments->first()->tercero;
            $account = ProveedorCuentaBancaria::with('entidad_financiera')
                ->where('tercero_id', $thirdPartyId)
                ->where('estado', 'Activo')
                ->orderBy('id', 'DESC')
                ->first();

            try {
                if (is_null($thirdParty)) {
                    throw new \InvalidArgumentException('Tercero no encontrado.');
                }
                if (is_null($account)) {
                    throw new \InvalidArgumentException('No tiene una cuenta bancaria activa.');
                }

                $eligible[] = [
                    'third_party' => $thirdParty,
                    'account' => $account,
                    'identification' => $this->identificationNumber($thirdParty),
                    'identification_type' => $this->identificationType($thirdParty),
                    'account_number' => $this->digits($account->numero_cuenta, 16, 'número de cuenta del beneficiario'),
                    'account_type' => $this->accountType($account->tipo_cuenta),
                    'bank_code' => $this->leftPadNumber($account->entidad_financiera_id, 6, 'código del banco beneficiario'),
                    'amount_cents' => $this->moneyToCents($thirdPartyPayments->sum('abono'))
                ];
            } catch (\InvalidArgumentException $exception) {
                $name = is_null($thirdParty) ? 'Tercero #' . $thirdPartyId : $thirdParty->descripcion;
                $omitted[] = $name . ': ' . $exception->getMessage();
            }
        }

        return compact('eligible', 'omitted');
    }

    protected function controlRecord($payment, $company, $serviceCode, $totalCents, $count, \DateTime $now)
    {
        $sourceAccount = $payment->cuenta_bancaria;

        return 'RC'
            . $this->leftPadNumber($this->identificationNumber($company), 16, 'NIT de la empresa')
            . $serviceCode
            . $serviceCode
            . $this->leftPadNumber($this->digits($sourceAccount->descripcion, 16, 'cuenta de la empresa'), 16, 'cuenta de la empresa')
            . $this->accountType($sourceAccount->tipo_cuenta)
            . '000051'
            . $this->leftPadNumber($totalCents, 18, 'valor total')
            . $this->leftPadNumber($count, 6, 'cantidad de traslados')
            . $now->format('Ymd')
            . $now->format('His')
            . '0000'
            . '9999'
            . '00000000'
            . '000000'
            . '00'
            . $this->identificationType($company)
            . str_repeat('0', 12)
            . '0000'
            . str_repeat('0', 40);
    }

    protected function transferRecord(array $beneficiary)
    {
        return 'TR'
            . $this->leftPadNumber($beneficiary['identification'], 16, 'identificación del beneficiario')
            . str_repeat('0', 16)
            . $this->leftPadNumber($beneficiary['account_number'], 16, 'cuenta del beneficiario')
            . $beneficiary['account_type']
            . $beneficiary['bank_code']
            . $this->leftPadNumber($beneficiary['amount_cents'], 18, 'valor del traslado')
            . '000000'
            . $beneficiary['identification_type']
            . '1'
            . '9999'
            . str_repeat('0', 40)
            . str_repeat('0', 18)
            . '00000000'
            . '0000'
            . '0000'
            . '0000000';
    }

    protected function identificationType($party)
    {
        $abbreviation = is_null($party->tipo_doc_identidad)
            ? ''
            : strtoupper(trim($party->tipo_doc_identidad->abreviatura));

        $types = [
            'CC' => '01', 'CE' => '02', 'NIT' => '03', 'TI' => '04',
            'PAS' => '05', 'RC' => '13'
        ];

        if (!isset($types[$abbreviation])) {
            throw new \InvalidArgumentException('Tipo de identificación no soportado por Davivienda: ' . $abbreviation . '.');
        }

        return $types[$abbreviation];
    }

    protected function identificationNumber($party)
    {
        $number = preg_replace('/\D+/', '', (string) $party->numero_identificacion);
        $abbreviation = is_null($party->tipo_doc_identidad)
            ? ''
            : strtoupper(trim($party->tipo_doc_identidad->abreviatura));

        if ($abbreviation === 'NIT') {
            $checkDigit = preg_replace('/\D+/', '', (string) $party->digito_verificacion);
            if ($checkDigit !== '') {
                $number .= $checkDigit;
            }
        }

        return $this->digits($number, 16, 'número de identificación');
    }

    protected function accountType($type)
    {
        $normalized = strtolower(trim((string) $type));
        if ($normalized === 'ahorros' || $normalized === 'ca' || $normalized === '01') {
            return 'CA';
        }
        if ($normalized === 'corriente' || $normalized === 'cc' || $normalized === '00') {
            return 'CC';
        }

        throw new \InvalidArgumentException('Tipo de cuenta no soportado por Davivienda: ' . $type . '.');
    }

    protected function digits($value, $maxLength, $field)
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '' || strlen($digits) > $maxLength) {
            throw new \InvalidArgumentException('El ' . $field . ' debe contener entre 1 y ' . $maxLength . ' dígitos.');
        }

        return $digits;
    }

    protected function leftPadNumber($value, $length, $field)
    {
        $digits = $this->digits($value, $length, $field);

        return str_pad($digits, $length, '0', STR_PAD_LEFT);
    }

    protected function moneyToCents($value)
    {
        $cents = (int) round((float) $value * 100);
        if ($cents <= 0) {
            throw new \InvalidArgumentException('El valor del traslado debe ser mayor a cero.');
        }

        return $cents;
    }

    protected function sumCents(array $beneficiaries)
    {
        $total = 0;
        foreach ($beneficiaries as $beneficiary) {
            $total += $beneficiary['amount_cents'];
        }

        return $total;
    }

    protected function centsToDecimal($cents)
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
