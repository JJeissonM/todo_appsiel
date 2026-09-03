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

    public function closedForOperationalDate($companyId, $pdvId, $cashBoxId, $date, $cashierId = null, $limit = null)
    {
        $query = $this->eligibleQuery($companyId, $pdvId, $cashBoxId, $cashierId);
        if (is_null($query)) {
            return collect(array());
        }

        $query->where('fecha_operativa', trim((string)$date))
            ->orderBy('cerrado_en', 'DESC')
            ->orderBy('id', 'DESC');
        if ((int)$limit > 0) {
            $query->limit((int)$limit);
        }
        return $query->get();
    }

    public function findEligible($companyId, $pdvId, $cashBoxId, $turnId, $cashierId = null)
    {
        $query = $this->eligibleQuery($companyId, $pdvId, $cashBoxId, $cashierId);
        return is_null($query) ? null : $query->where('id', (int)$turnId)->first();
    }

    /**
     * Turnos cerrados que tuvieron actividad en una caja, aunque ésta no sea la
     * caja predeterminada del PDV (por ejemplo, CAJA GENERAL como destino de un
     * traslado). También incluye la asociación directa guardada en el turno.
     */
    public function closedForCashBox($companyId, $cashBoxId, $date = null, $cashierId = null, $limit = null)
    {
        $query = $this->eligibleCashBoxQuery($companyId, $cashBoxId, $cashierId);
        if (is_null($query)) {
            return collect(array());
        }
        if (!is_null($date) && trim((string)$date) !== '') {
            $query->where('fecha_operativa', trim((string)$date));
        }
        $query->orderBy('cerrado_en', 'DESC')->orderBy('id', 'DESC');
        if ((int)$limit > 0) {
            $query->limit((int)$limit);
        }
        return $query->get();
    }

    public function findEligibleForCashBox($companyId, $cashBoxId, $turnId, $cashierId = null)
    {
        $query = $this->eligibleCashBoxQuery($companyId, $cashBoxId, $cashierId);
        return is_null($query) ? null : $query->where('id', (int)$turnId)->first();
    }

    public function searchClosedForCashBox($companyId, $cashBoxId, $search, $date = null, $limit = 20)
    {
        $query = $this->eligibleCashBoxQuery($companyId, $cashBoxId);
        if (is_null($query)) {
            return collect(array());
        }

        $search = trim((string)$search);
        if ($search === '') {
            return collect(array());
        }
        if (!is_null($date) && trim((string)$date) !== '') {
            $query->where('fecha_operativa', trim((string)$date));
        }

        $like = '%' . str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $search) . '%';
        $query->where(function ($turns) use ($like) {
            $turns->where('codigo', 'LIKE', $like)
                ->orWhere('fecha_operativa', 'LIKE', $like)
                ->orWhere('estado', 'LIKE', $like);
        });

        return $query->orderBy('cerrado_en', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(max(1, min((int)$limit, 50)))
            ->get();
    }

    protected function eligibleCashBoxQuery($companyId, $cashBoxId, $cashierId = null)
    {
        $companyId = (int)$companyId;
        $cashBoxId = (int)$cashBoxId;
        $cashierId = (int)$cashierId;
        if ($companyId <= 0 || $cashBoxId <= 0
            || !TesoCaja::where('core_empresa_id', $companyId)->where('id', $cashBoxId)->exists()) {
            return null;
        }

        $query = TurnoOperativo::where('core_empresa_id', $companyId)
            ->whereIn('estado', $this->closedStates)
            ->whereNotNull('cerrado_en')
            ->where(function ($turns) use ($cashBoxId) {
                $turns->where('teso_caja_id', $cashBoxId)
                    // Laravel 5.2 presenta un defecto interno con
                    // orWhereExists(); la subconsulta parametrizada conserva
                    // el mismo alcance sin interpolar valores.
                    ->orWhereRaw(
                        'EXISTS (SELECT 1 FROM teso_movimientos'
                        . ' WHERE teso_movimientos.turno_operativo_id = core_turnos_operativos.id'
                        . ' AND teso_movimientos.teso_caja_id = ?)',
                        array($cashBoxId)
                    );
            });

        if ($cashierId > 0) {
            $query->where('abierto_por', $cashierId);
        }
        return $query;
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
