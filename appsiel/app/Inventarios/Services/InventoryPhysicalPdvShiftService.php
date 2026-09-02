<?php

namespace App\Inventarios\Services;

use App\Core\TurnoOperativo;
use App\VentasPos\AperturaEncabezado;
use App\VentasPos\CierreEncabezado;
use App\VentasPos\Pdv;
use Carbon\Carbon;

class InventoryPhysicalPdvShiftService
{
    const CLOSING_CONTROL_OPERATION = 'inventory_physical_closing_control';

    /**
     * Último turno efectivamente cerrado por el cajero. Se usa únicamente para
     * el conteo físico de entrega posterior al cierre; no habilita operaciones
     * comerciales ordinarias sobre turnos históricos.
     */
    public function lastClosedTurnForCashier($companyId, $userId, $pdvId = null)
    {
        $companyId = (int)$companyId;
        $userId = (int)$userId;
        $pdvId = (int)$pdvId;
        if ($companyId <= 0 || $userId <= 0) {
            return null;
        }

        $query = TurnoOperativo::where('core_empresa_id', $companyId)
            ->where('abierto_por', $userId)
            ->where('estado', TurnoOperativo::ESTADO_CERRADO)
            ->whereNotNull('abierto_en')
            ->whereNotNull('cerrado_en');

        if ($pdvId > 0) {
            $query->where('contexto_tipo', 'pdv')->where('contexto_id', $pdvId);
        }

        // Se validan también el PDV y el orden cronológico para no ofrecer un
        // turno incompleto aunque existan datos históricos defectuosos.
        foreach ($query->orderBy('cerrado_en', 'DESC')->orderBy('id', 'DESC')->limit(20)->get() as $turn) {
            if (!is_null($this->findForTurn($companyId, $turn->id))) {
                return $turn;
            }
        }

        return null;
    }

    /**
     * Resuelve el rango desde la entidad de turno explícita. El usuario que
     * ejecuta el inventario no interviene en la pertenencia del rango.
     */
    public function findForTurn($companyId, $turnId)
    {
        $companyId = (int)$companyId;
        $turnId = (int)$turnId;
        if ($companyId <= 0 || $turnId <= 0) {
            return null;
        }

        $turn = TurnoOperativo::where('core_empresa_id', $companyId)
            ->where('id', $turnId)
            ->where('contexto_tipo', 'pdv')
            ->first();
        if (is_null($turn) || empty($turn->abierto_en) || empty($turn->cerrado_en)) {
            return null;
        }

        $pdv = Pdv::where('core_empresa_id', $companyId)
            ->where('id', (int)$turn->contexto_id)
            ->first();
        if (is_null($pdv)) {
            return null;
        }

        $openingAt = Carbon::parse($turn->abierto_en);
        $closingAt = Carbon::parse($turn->cerrado_en);
        if ($closingAt->lt($openingAt)) {
            return null;
        }

        return array(
            'fecha' => $turn->fecha_operativa,
            'fecha_operativa' => $turn->fecha_operativa,
            'hora_inicio' => $openingAt->format('H:i:s'),
            'hora_finalizacion' => $closingAt->format('H:i:s'),
            'fecha_hora_apertura' => $openingAt->format('Y-m-d H:i:s'),
            'fecha_hora_cierre' => $closingAt->format('Y-m-d H:i:s'),
            'pdv_id' => (int)$pdv->id,
            'pdv' => $pdv->descripcion,
            'turno_operativo_id' => (int)$turn->id,
            'codigo_turno' => $turn->codigo,
            'fuente' => 'TURNO_OPERATIVO',
        );
    }

    public function findForUserWarehouseDate($companyId, $userId, $userEmail, $warehouseId, $date)
    {
        $companyId = (int)$companyId;
        $userId = (int)$userId;
        $warehouseId = (int)$warehouseId;
        $date = trim((string)$date);

        if ($companyId <= 0 || $userId <= 0 || $warehouseId <= 0 || !$this->isValidDate($date)) {
            return null;
        }

        $pdvIds = Pdv::where('core_empresa_id', $companyId)
            ->where('bodega_default_id', $warehouseId)
            ->lists('id')
            ->toArray();

        if (empty($pdvIds)) {
            return null;
        }

        $openings = AperturaEncabezado::where('core_empresa_id', $companyId)
            ->where('fecha', $date)
            ->whereIn('pdv_id', $pdvIds)
            ->where(function ($query) use ($userId, $userEmail) {
                $query->where('cajero_id', $userId);
                if (trim((string)$userEmail) !== '') {
                    $query->orWhere('creado_por', $userEmail);
                }
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'fecha', 'cajero_id', 'pdv_id', 'created_at']);

        if ($openings->isEmpty()) {
            return null;
        }

        $firstOpeningAt = $openings->min('created_at');
        $lastPossibleClosingAt = Carbon::parse($openings->max('created_at'))->addDay();

        $closings = CierreEncabezado::where('core_empresa_id', $companyId)
            ->whereIn('pdv_id', $pdvIds)
            ->where('created_at', '>=', $firstOpeningAt)
            ->where('created_at', '<=', $lastPossibleClosingAt)
            ->where(function ($query) use ($userId, $userEmail) {
                $query->where('cajero_id', $userId);
                if (trim((string)$userEmail) !== '') {
                    $query->orWhere('creado_por', $userEmail);
                }
            })
            ->orderBy('created_at')
            ->get(['id', 'fecha', 'cajero_id', 'pdv_id', 'created_at']);

        $shift = $this->selectLastClosedShift($openings->all(), $closings->all());
        if (is_null($shift)) {
            return null;
        }

        $pdv = Pdv::find($shift['pdv_id']);
        $shift['pdv'] = is_null($pdv) ? '' : $pdv->descripcion;

        return $shift;
    }

    /**
     * Empareja cada apertura con el primer cierre anterior a la siguiente
     * apertura del mismo cajero y PDV. Así no se reutiliza un cierre de otro turno.
     */
    public function selectLastClosedShift(array $openings, array $closings)
    {
        usort($openings, function ($a, $b) {
            return strcmp($this->timestamp($b->created_at), $this->timestamp($a->created_at));
        });

        usort($closings, function ($a, $b) {
            return strcmp($this->timestamp($a->created_at), $this->timestamp($b->created_at));
        });

        $newerOpeningByPdvCashier = [];

        foreach ($openings as $opening) {
            $openingAt = Carbon::parse($this->timestamp($opening->created_at));
            $key = $opening->pdv_id . ':' . $opening->cajero_id;
            $newerOpeningAt = isset($newerOpeningByPdvCashier[$key])
                ? Carbon::parse($newerOpeningByPdvCashier[$key])
                : null;
            $maximumClosingAt = $openingAt->copy()->addDay();

            foreach ($closings as $closing) {
                if ((int)$closing->pdv_id !== (int)$opening->pdv_id
                    || (int)$closing->cajero_id !== (int)$opening->cajero_id) {
                    continue;
                }

                $closingAt = Carbon::parse($this->timestamp($closing->created_at));
                if ($closingAt->lt($openingAt) || $closingAt->gt($maximumClosingAt)) {
                    continue;
                }

                if (!is_null($newerOpeningAt) && !$closingAt->lt($newerOpeningAt)) {
                    continue;
                }

                return [
                    'fecha' => $openingAt->format('Y-m-d'),
                    'hora_inicio' => $openingAt->format('H:i:s'),
                    'hora_finalizacion' => $closingAt->format('H:i:s'),
                    'fecha_hora_apertura' => $openingAt->format('Y-m-d H:i:s'),
                    'fecha_hora_cierre' => $closingAt->format('Y-m-d H:i:s'),
                    'pdv_id' => (int)$opening->pdv_id,
                    'apertura_id' => (int)$opening->id,
                    'cierre_id' => (int)$closing->id
                ];
            }

            $newerOpeningByPdvCashier[$key] = $openingAt->format('Y-m-d H:i:s');
        }

        return null;
    }

    private function timestamp($value)
    {
        return $value instanceof \DateTimeInterface ? $value->format('Y-m-d H:i:s') : (string)$value;
    }

    private function isValidDate($date)
    {
        $dateObject = \DateTime::createFromFormat('Y-m-d', $date);
        return $dateObject !== false && $dateObject->format('Y-m-d') === $date;
    }
}
