<?php

namespace App\Compras\Services;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMotivo;
use App\Contabilidad\ContabCuenta;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CompraConfirmationPaymentService
{
    public function fromJson($json)
    {
        if (!is_scalar($json) || substr(ltrim((string)$json), 0, 1) !== '[') {
            throw new \InvalidArgumentException('Los medios de pago tienen un formato inválido.');
        }
        $rows = json_decode((string)$json, true);
        if (!is_array($rows) || array_values($rows) !== $rows) {
            throw new \InvalidArgumentException('Los medios de pago tienen un formato inválido.');
        }
        // Compatibilidad con el modal anterior, que enviaba una última fila de totales.
        if ($rows && !isset(end($rows)['valor_recaudo']) && isset(end($rows)['teso_medio_recaudo_id'])
            && end($rows)['teso_medio_recaudo_id'] === '') {
            array_pop($rows);
        }
        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw new \InvalidArgumentException('Cada medio de pago debe ser un registro válido.');
            }
            $payment = [];
            foreach (['teso_medio_recaudo_id','teso_motivo_id','teso_caja_id','teso_cuenta_bancaria_id'] as $field) {
                $value = isset($row[$field]) ? $row[$field] : '0';
                if (!is_scalar($value) || !preg_match('/^\d+(?:-.*)?$/u', (string)$value)) {
                    throw new \InvalidArgumentException('Identificador inválido en los medios de pago.');
                }
                $payment[$field] = (int)explode('-', (string)$value, 2)[0];
            }
            $value = isset($row['valor_recaudo']) ? $row['valor_recaudo'] : (isset($row['valor']) && is_scalar($row['valor']) ? ltrim((string)$row['valor'], '$') : null);
            if (!is_scalar($value) || !preg_match('/^\d+(?:\.\d{1,2})?$/', (string)$value)) {
                throw new \InvalidArgumentException('Ingrese importes positivos con máximo dos decimales.');
            }
            $payment['valor_recaudo'] = (float)$value;
            $result[] = $payment;
        }
        return $result;
    }

    public function validate(array $payments, $net, $companyId)
    {
        if (!$payments || !is_finite((float)$net) || $net <= 0) {
            throw new \InvalidArgumentException('Debe ingresar medios de pago para el valor neto de la factura de contado.');
        }
        $allowedBoxes = TesoCaja::get_cajas_permitidas()->pluck('id')->all();
        $totalCents = 0;
        foreach ($payments as $payment) {
            if (!is_array($payment)) {
                throw new \InvalidArgumentException('Medio de pago inválido.');
            }
            foreach (['teso_medio_recaudo_id','teso_motivo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_recaudo'] as $field) {
                if (!isset($payment[$field]) || !is_numeric($payment[$field]) || !is_finite((float)$payment[$field])) {
                    throw new \InvalidArgumentException('Medio de pago incompleto o inválido.');
                }
            }
            $amount = (float)$payment['valor_recaudo'];
            if ($amount <= 0 || abs($amount - round($amount, 2)) > 0.000001) {
                throw new \InvalidArgumentException('Cada pago debe ser positivo y tener máximo dos decimales.');
            }
            $medium = TesoMedioRecaudo::where('estado', 'Activo')->find($payment['teso_medio_recaudo_id']);
            $motive = TesoMotivo::where('core_empresa_id', $companyId)->where('estado', 'Activo')
                ->where('movimiento', 'salida')->find($payment['teso_motivo_id']);
            if (!$medium || !$motive) {
                throw new \InvalidArgumentException('Seleccione un medio activo y un motivo de salida válido para la empresa.');
            }
            $boxId = (int)$payment['teso_caja_id'];
            $bankId = (int)$payment['teso_cuenta_bancaria_id'];
            if (($boxId > 0) === ($bankId > 0)) {
                throw new \InvalidArgumentException('Cada pago debe tener una caja o una cuenta bancaria.');
            }
            if ($medium->comportamiento === 'Efectivo' && !$boxId) {
                throw new \InvalidArgumentException('El pago en efectivo requiere una caja.');
            }
            if ($medium->comportamiento === 'Tarjeta bancaria' && !$bankId) {
                throw new \InvalidArgumentException('El pago bancario requiere una cuenta bancaria.');
            }
            $destination = $boxId
                ? TesoCaja::where('core_empresa_id', $companyId)->where('estado', 'Activo')->find($boxId)
                : TesoCuentaBancaria::where('core_empresa_id', $companyId)->where('estado', 'Activo')->find($bankId);
            if (!$destination || ($boxId && !in_array($boxId, $allowedBoxes))) {
                throw new \InvalidArgumentException('La caja o cuenta bancaria no está activa o no está permitida para el usuario y la empresa.');
            }
            if (!ContabCuenta::where('core_empresa_id', $companyId)->where('estado', 'Activo')->find($destination->contab_cuenta_id)) {
                throw new \InvalidArgumentException('Configure una cuenta contable activa para cada destino de pago.');
            }
            if (Schema::hasTable('teso_medios_recaudo_destinos')
                && DB::table('teso_medios_recaudo_destinos')->where('estado', 'Activo')->exists()
                && !DB::table('teso_medios_recaudo_destinos')->where('estado', 'Activo')
                    ->where('teso_medio_recaudo_id', $medium->id)
                    ->where(function ($q) use ($boxId) { $q->where('teso_caja_id', $boxId); if (!$boxId) $q->orWhereNull('teso_caja_id'); })
                    ->where(function ($q) use ($bankId) { $q->where('teso_cuenta_bancaria_id', $bankId); if (!$bankId) $q->orWhereNull('teso_cuenta_bancaria_id'); })->exists()) {
                throw new \InvalidArgumentException('El destino seleccionado no está habilitado para ese medio de pago.');
            }
            $totalCents += (int)round($amount * 100);
        }
        if ($totalCents !== (int)round($net * 100)) {
            throw new \InvalidArgumentException('El total de los medios de pago no coincide con el valor neto a pagar de la factura.');
        }
        return $payments;
    }
}
