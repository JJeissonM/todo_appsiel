<?php

namespace App\VentasPos\Services;

use App\VentasPos\AperturaEncabezado;
use App\VentasPos\CierreEncabezado;
use App\VentasPos\Pdv;

class CashRegisterShiftService
{
    public function getOperationalShift(Pdv $pdv, $turnoId = null)
    {
        $query = \App\Core\TurnoOperativo::where('core_empresa_id', $pdv->core_empresa_id)
            ->where('contexto_tipo', 'pdv')
            ->where('contexto_id', $pdv->id);

        if (is_null($turnoId)) {
            $query->where('estado', \App\Core\TurnoOperativo::ESTADO_ABIERTO);
        } else {
            $query->where('id', (int)$turnoId);
        }

        $turno = $query->orderBy('id', 'DESC')->first();
        if (is_null($turno)) {
            return null;
        }

        return array(
            'turno_operativo_id' => (int)$turno->id,
            'operational_date' => $turno->fecha_operativa,
            'opening_at' => (string)$turno->abierto_en,
            'closing_at' => is_null($turno->cerrado_en) ? '' : (string)$turno->cerrado_en,
            'cash_base' => (float)$turno->saldo_inicial,
            'has_opening' => true,
            'has_closing' => !is_null($turno->cerrado_en),
        );
    }

    public function getDayRange(Pdv $pdv, $date)
    {
        $date = $this->normalizeDate($date);
        if ($date == '') {
            return null;
        }

        $dayStart = $date . ' 00:00:00';
        $dayEnd = $date . ' 23:59:59';

        $firstOpening = AperturaEncabezado::where('pdv_id', $pdv->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at', 'ASC')
            ->first();

        $lastClosing = CierreEncabezado::where('pdv_id', $pdv->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at', 'DESC')
            ->first();

        return [
            'opening_at' => is_null($firstOpening) ? '' : (string)$firstOpening->created_at,
            'closing_at' => is_null($lastClosing) ? '' : (string)$lastClosing->created_at,
            'cash_base' => is_null($firstOpening) ? 0 : (float)$firstOpening->efectivo_base,
            'has_opening' => !is_null($firstOpening),
            'has_closing' => !is_null($lastClosing)
        ];
    }

    public function normalizeEditableRange($date, $openingAt, $closingAt)
    {
        $date = $this->normalizeDate($date);
        $openingAt = $this->normalizeDateTime($openingAt);
        $closingAt = $this->normalizeDateTime($closingAt);

        if ($date == '' || ($openingAt == '' && $closingAt == '')) {
            return null;
        }

        if ($openingAt == '' || $closingAt == '') {
            throw new \UnexpectedValueException('Debe ingresar tanto la fecha y hora de apertura como la de cierre.');
        }

        if (substr($openingAt, 0, 10) != $date) {
            throw new \UnexpectedValueException('La apertura debe pertenecer a la fecha seleccionada para el arqueo.');
        }

        if (strtotime($closingAt) < strtotime($openingAt)) {
            throw new \UnexpectedValueException('La fecha y hora de cierre no puede ser anterior a la apertura.');
        }

        $maximumClosing = date('Y-m-d 23:59:59', strtotime($date . ' +1 day'));
        if (strtotime($closingAt) > strtotime($maximumClosing)) {
            throw new \UnexpectedValueException('El cierre no puede superar el día siguiente a la fecha del arqueo.');
        }

        return [
            'opening_at' => $openingAt,
            'closing_at' => $closingAt
        ];
    }

    protected function normalizeDate($date)
    {
        $date = trim((string)$date);
        $dateObject = \DateTime::createFromFormat('Y-m-d', $date);

        if ($dateObject === false || $dateObject->format('Y-m-d') !== $date) {
            return '';
        }

        return $date;
    }

    protected function normalizeDateTime($dateTime)
    {
        $dateTime = trim(str_replace('T', ' ', (string)$dateTime));
        if ($dateTime == '') {
            return '';
        }

        foreach (['Y-m-d H:i:s', 'Y-m-d H:i'] as $format) {
            $dateObject = \DateTime::createFromFormat($format, $dateTime);
            if ($dateObject !== false && $dateObject->format($format) === $dateTime) {
                return $dateObject->format('Y-m-d H:i:s');
            }
        }

        return '';
    }
}
