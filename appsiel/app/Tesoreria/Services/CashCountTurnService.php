<?php

namespace App\Tesoreria\Services;

use App\Core\TurnoOperativo;
use App\Tesoreria\TesoCaja;
use App\VentasPos\Pdv;

class CashCountTurnService
{
    protected $closedStates = array(
        TurnoOperativo::ESTADO_CERRADO,
        TurnoOperativo::ESTADO_AUDITANDO,
        TurnoOperativo::ESTADO_AUDITADO,
    );

    public function latestClosed($companyId, $pdvId, $cashBoxId, $cashierId = null)
    {
        $query = $this->eligibleQuery($companyId, $pdvId, $cashBoxId, $cashierId);
        return is_null($query)
            ? null
            : $query->orderBy('cerrado_en', 'DESC')->orderBy('id', 'DESC')->first();
    }

    public function closedForOperationalDate($companyId, $pdvId, $cashBoxId, $date, $cashierId = null)
    {
        $query = $this->eligibleQuery($companyId, $pdvId, $cashBoxId, $cashierId);
        if (is_null($query)) {
            return collect(array());
        }

        return $query->where('fecha_operativa', trim((string)$date))
            ->orderBy('cerrado_en', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function findEligible($companyId, $pdvId, $cashBoxId, $turnId, $cashierId = null)
    {
        $query = $this->eligibleQuery($companyId, $pdvId, $cashBoxId, $cashierId);
        return is_null($query) ? null : $query->where('id', (int)$turnId)->first();
    }

    protected function eligibleQuery($companyId, $pdvId, $cashBoxId, $cashierId = null)
    {
        $companyId = (int)$companyId;
        $pdvId = (int)$pdvId;
        $cashBoxId = (int)$cashBoxId;
        $cashierId = (int)$cashierId;
        if ($companyId <= 0 || $pdvId <= 0 || $cashBoxId <= 0) {
            return null;
        }

        $pdv = Pdv::where('core_empresa_id', $companyId)->where('id', $pdvId)->first();
        $cashBoxExists = TesoCaja::where('core_empresa_id', $companyId)->where('id', $cashBoxId)->exists();
        if (is_null($pdv) || !$cashBoxExists || (int)$pdv->caja_default_id !== $cashBoxId) {
            return null;
        }

        $query = TurnoOperativo::where('core_empresa_id', $companyId)
            ->where('contexto_tipo', 'pdv')
            ->where('contexto_id', $pdvId)
            ->whereIn('estado', $this->closedStates)
            ->whereNotNull('cerrado_en')
            ->where(function ($turns) use ($cashBoxId) {
                // Los turnos anteriores a la persistencia de la caja conservan
                // el PDV como identidad; para los nuevos se exige la caja exacta.
                $turns->where('teso_caja_id', $cashBoxId)->orWhereNull('teso_caja_id');
            });

        if ($cashierId > 0) {
            $query->where('abierto_por', $cashierId);
        }
        return $query;
    }
}
