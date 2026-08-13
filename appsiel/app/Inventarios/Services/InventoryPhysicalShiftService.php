<?php

namespace App\Inventarios\Services;

class InventoryPhysicalShiftService
{
    public function isEnabled()
    {
        return (int)config('inventarios.usar_inventario_fisico_por_horas', 0) === 1;
    }

    /**
     * Construye el rango real del turno. Si el cierre es menor que la apertura,
     * se interpreta como un turno nocturno que termina al dia siguiente.
     */
    public function getRange($date, $openingTime, $closingTime)
    {
        $date = $this->normalizeDate($date);
        $openingTime = $this->normalizeTime($openingTime);
        $closingTime = $this->normalizeTime($closingTime);

        if ($date == '' || $openingTime == '' || $closingTime == '') {
            return null;
        }

        $openingAt = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $openingTime);
        $closingAt = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $closingTime);

        if ($openingAt === false || $closingAt === false) {
            return null;
        }

        if ($closingAt < $openingAt) {
            $closingAt->modify('+1 day');
        }

        return [
            'opening_at' => $openingAt->format('Y-m-d H:i:s'),
            'closing_at' => $closingAt->format('Y-m-d H:i:s'),
            'opening_date' => $openingAt->format('Y-m-d'),
            'closing_date' => $closingAt->format('Y-m-d')
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

    protected function normalizeTime($time)
    {
        $time = trim((string)$time);
        if (strlen($time) === 5) {
            $time .= ':00';
        }

        $timeObject = \DateTime::createFromFormat('H:i:s', $time);
        if ($timeObject === false || $timeObject->format('H:i:s') !== $time) {
            return '';
        }

        return $time;
    }
}
