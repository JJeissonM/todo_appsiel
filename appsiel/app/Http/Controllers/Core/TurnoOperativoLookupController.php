<?php

namespace App\Http\Controllers\Core;

use App\Core\Services\TurnoModeResolver;
use App\Core\TurnoOperativo;
use App\Http\Controllers\Controller;
use App\Inventarios\Services\InventoryPhysicalPdvShiftService;
use App\Tesoreria\TesoDocEncabezadoTraslado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TurnoOperativoLookupController extends Controller
{
    const RESULT_LIMIT = 20;
    const CANDIDATE_LIMIT = 100;

    protected $modeResolver;

    public function __construct(TurnoModeResolver $modeResolver)
    {
        $this->middleware('auth');
        $this->modeResolver = $modeResolver;
    }

    public function suggestions(Request $request)
    {
        $module = trim((string)$request->input('modulo'));
        if (!$this->isIntegratedModule($module)) {
            return response('<div class="list-group"><span class="list-group-item">Módulo de turnos inválido.</span></div>', 422);
        }

        $companyId = (int)Auth::user()->empresa_id;
        $search = trim((string)$request->input('texto_busqueda'));
        $pdvId = (int)$request->input('pdv_id');
        $states = $this->allowedStates();

        $query = TurnoOperativo::query()
            ->leftJoin('vtas_pos_puntos_de_ventas AS turno_pdv', function ($join) {
                $join->on('turno_pdv.id', '=', 'core_turnos_operativos.contexto_id')
                    ->on('turno_pdv.core_empresa_id', '=', 'core_turnos_operativos.core_empresa_id');
            })
            ->where('core_turnos_operativos.core_empresa_id', $companyId)
            ->whereIn('core_turnos_operativos.estado', $states)
            ->select('core_turnos_operativos.*', 'turno_pdv.descripcion AS pdv_descripcion');

        if ($pdvId > 0 && !config('turnos.simple_company_mode', false)) {
            $query->where('core_turnos_operativos.contexto_tipo', 'pdv')
                ->where('core_turnos_operativos.contexto_id', $pdvId);
        }
        if ($search !== '') {
            $like = '%' . $search . '%';
            $query->where(function ($inner) use ($like) {
                $inner->where('core_turnos_operativos.codigo', 'LIKE', $like)
                    ->orWhere('core_turnos_operativos.fecha_operativa', 'LIKE', $like)
                    ->orWhere('core_turnos_operativos.estado', 'LIKE', $like)
                    ->orWhere('core_turnos_operativos.contexto_tipo', 'LIKE', $like)
                    ->orWhere('core_turnos_operativos.contexto_id', 'LIKE', $like)
                    ->orWhere('turno_pdv.descripcion', 'LIKE', $like);
            });
        }

        $turns = array();
        foreach ($query->orderBy('core_turnos_operativos.abierto_en', 'DESC')
            ->limit(self::CANDIDATE_LIMIT)->get() as $turn) {
            if (!$this->modeResolver->enabled(
                $companyId,
                $module,
                $turn->contexto_tipo,
                (int)$turn->contexto_id
            )) {
                continue;
            }
            $turns[] = $turn;
            if (count($turns) >= self::RESULT_LIMIT) {
                break;
            }
        }

        return response($this->renderSuggestions($turns));
    }

    public function validateSelection(Request $request)
    {
        $module = trim((string)$request->input('modulo'));
        if (!$this->isIntegratedModule($module)) {
            return $this->validationError('El módulo indicado no admite turnos operativos.');
        }

        $companyId = (int)Auth::user()->empresa_id;
        $turnId = (int)$request->input('turno_operativo_id');
        $locked = $this->selectionLockedForCurrentUser();
        $physicalClosingControl = $locked
            && $module === 'inventarios'
            && $request->input('operacion_turno') === InventoryPhysicalPdvShiftService::CLOSING_CONTROL_OPERATION;
        $cashTransferAfterClosing = $locked
            && $module === 'tesoreria'
            && $request->input('operacion_turno') === TesoDocEncabezadoTraslado::POST_CLOSING_OPERATION;
        $closedCashierOperation = $physicalClosingControl || $cashTransferAfterClosing;

        if ($turnId <= 0) {
            if (!$locked && config('turnos.simple_company_mode', false)) {
                return response()->json(array('ok' => true, 'turno_operativo_id' => null));
            }
            $closed = $cashTransferAfterClosing
                ? $this->lastClosedCashierTurn($companyId, $module)
                : null;
            if (!is_null($closed)) {
                return response()->json(array(
                    'ok' => true,
                    'turno_operativo_id' => (int)$closed->id,
                    'estado' => $closed->estado,
                    'fuente' => 'LAST_CLOSED_CASHIER',
                ));
            }

            $current = $this->openTurnForCurrentCashier($companyId);
            if (is_null($current)) {
                if ($closedCashierOperation) {
                    $closed = $this->lastClosedCashierTurn($companyId, $module);
                    if (!is_null($closed)) {
                        return response()->json(array(
                            'ok' => true,
                            'turno_operativo_id' => (int)$closed->id,
                            'estado' => $closed->estado,
                            'fuente' => 'LAST_CLOSED_CASHIER',
                        ));
                    }
                }
                return $this->validationError($closedCashierOperation
                    ? ($cashTransferAfterClosing
                        ? 'No existe un último turno cerrado válido del cajero para realizar el traslado de efectivo.'
                        : 'No existe un último turno cerrado válido del cajero para realizar el control físico de entrega.')
                    : 'No existe un turno abierto asignado al usuario cajero. Debe realizar la apertura antes de continuar.');
            }
            return response()->json(array('ok' => true, 'turno_operativo_id' => (int)$current->id, 'estado' => $current->estado));
        }

        $turn = TurnoOperativo::where('id', $turnId)
            ->where('core_empresa_id', $companyId)
            ->first();
        if (is_null($turn)) {
            return $this->validationError('El turno seleccionado no existe o pertenece a otra empresa.');
        }

        if ($locked) {
            $current = $this->openTurnForCurrentCashier($companyId);
            if ($cashTransferAfterClosing || is_null($current) || (int)$current->id !== (int)$turn->id) {
                $closed = $closedCashierOperation
                    ? $this->lastClosedCashierTurn($companyId, $module)
                    : null;
                if (is_null($closed) || (int)$closed->id !== (int)$turn->id) {
                    return $this->validationError('El turno asignado al cajero ya no está abierto o no corresponde al usuario actual.');
                }
                return response()->json(array(
                    'ok' => true,
                    'turno_operativo_id' => (int)$closed->id,
                    'estado' => $closed->estado,
                    'fuente' => 'LAST_CLOSED_CASHIER',
                ));
            }
        }

        if ($turn->estado === TurnoOperativo::ESTADO_ABIERTO) {
            return response()->json(array('ok' => true, 'turno_operativo_id' => (int)$turn->id, 'estado' => $turn->estado));
        }
        if (in_array($turn->estado, array(TurnoOperativo::ESTADO_CERRADO, TurnoOperativo::ESTADO_AUDITADO), true)) {
            if (!Auth::user()->can('turnos.ajustes.registrar')) {
                return $this->validationError('No tiene permiso para registrar operaciones sobre un turno cerrado o auditado.');
            }
            if (trim((string)$request->input('turno_ajuste_motivo')) === '') {
                return $this->validationError('Debe indicar el motivo del ajuste para utilizar un turno cerrado o auditado.', 'turno_ajuste_motivo');
            }
            return response()->json(array('ok' => true, 'turno_operativo_id' => (int)$turn->id, 'estado' => $turn->estado));
        }

        return $this->validationError('El turno seleccionado está en estado ' . $turn->estado . ' y no admite nuevas operaciones.');
    }

    protected function allowedStates()
    {
        if (Auth::user()->can('turnos.ajustes.registrar')) {
            return array(
                TurnoOperativo::ESTADO_ABIERTO,
                TurnoOperativo::ESTADO_CERRADO,
                TurnoOperativo::ESTADO_AUDITADO,
            );
        }
        return array(TurnoOperativo::ESTADO_ABIERTO);
    }

    protected function isIntegratedModule($module)
    {
        $modules = (array)config('turnos.modules', array());
        return isset($modules[$module]) && !empty($modules[$module]['integrated']);
    }

    protected function renderSuggestions(array $turns)
    {
        $html = '<div class="list-group">';
        $last = count($turns) - 1;
        foreach ($turns as $index => $turn) {
            $context = $turn->contexto_tipo . ' ' . $turn->contexto_id;
            if ($turn->contexto_tipo === 'pdv' && !empty($turn->pdv_descripcion)) {
                $context = 'PDV ' . $turn->contexto_id . ' - ' . $turn->pdv_descripcion;
            }
            $label = $turn->codigo . ' | ' . $turn->estado;
            $html .= '<a class="list-group-item list-group-item-sugerencia' . ($index === 0 ? ' active' : '') . '"'
                . ' data-registro_id="' . (int)$turn->id . '"'
                . ' data-turno-estado="' . e($turn->estado) . '"'
                . ' data-primer_item="' . ($index === 0 ? 1 : 0) . '"'
                . ' data-ultimo_item="' . ($index === $last ? 1 : 0) . '"'
                . ' data-accion="na">' . e($label) . '</a>';
        }
        if (empty($turns)) {
            $html .= '<span class="list-group-item">No se encontraron turnos válidos.</span>';
        }
        return $html . '</div>';
    }

    protected function selectionLockedForCurrentUser()
    {
        foreach ((array)config('turnos.turn_selection_locked_roles', array()) as $role) {
            if (Auth::user()->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    protected function openTurnForCurrentCashier($companyId)
    {
        return TurnoOperativo::where('core_empresa_id', (int)$companyId)
            ->where('abierto_por', (int)Auth::id())
            ->abiertos()
            ->orderBy('id', 'DESC')
            ->first();
    }

    protected function lastClosedCashierTurn($companyId, $module)
    {
        $turn = app(InventoryPhysicalPdvShiftService::class)
            ->lastClosedTurnForCashier($companyId, (int)Auth::id());
        if (is_null($turn) || !$this->modeResolver->enabled(
            $companyId,
            $module,
            $turn->contexto_tipo,
            (int)$turn->contexto_id
        )) {
            return null;
        }
        return $turn;
    }

    protected function validationError($message, $field = 'turno_operativo_id')
    {
        return response()->json(array('ok' => false, 'message' => $message, 'field' => $field), 422);
    }

}
