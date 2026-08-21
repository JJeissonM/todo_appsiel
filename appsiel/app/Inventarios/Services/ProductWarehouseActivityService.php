<?php

namespace App\Inventarios\Services;

class ProductWarehouseActivityService
{
    protected $daysWithoutMovement;

    public function __construct($daysWithoutMovement = null)
    {
        if (is_null($daysWithoutMovement)) {
            $daysWithoutMovement = config('inventarios.dias_sin_movimiento_producto_bodega', 0);
        }

        $this->daysWithoutMovement = max(0, (int)$daysWithoutMovement);
    }

    public function isEnabled()
    {
        return $this->daysWithoutMovement > 0;
    }

    /**
     * Fecha que marca el limite de inactividad. Un ultimo movimiento igual o
     * anterior a esta fecha ya cumple los dias configurados y debe excluirse.
     */
    public function getInactivityLimitDate($cutoffDate)
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $cutoffDate = trim((string)$cutoffDate);
        $date = \DateTime::createFromFormat('Y-m-d', $cutoffDate);

        if ($date === false || $date->format('Y-m-d') !== $cutoffDate) {
            return null;
        }

        $date->modify('-' . $this->daysWithoutMovement . ' days');

        return $date->format('Y-m-d');
    }

    public function applyLastMovementFilter($query, $cutoffDate, $dateColumn = 'inv_movimientos.fecha')
    {
        $limitDate = $this->getInactivityLimitDate($cutoffDate);

        if (is_null($limitDate)) {
            return $query;
        }

        return $query->havingRaw('MAX(' . $dateColumn . ') > ?', [$limitDate]);
    }
}
