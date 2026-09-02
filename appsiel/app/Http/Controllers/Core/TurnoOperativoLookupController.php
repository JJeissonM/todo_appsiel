<?php

namespace App\Http\Controllers\Core;

use App\Core\Services\TurnoModeResolver;
use App\Core\TurnoOperativo;
use App\Http\Controllers\Controller;
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
            $label = $turn->codigo . ' | ' . $turn->fecha_operativa . ' | ' . $context . ' | ' . $turn->estado;
            $html .= '<a class="list-group-item list-group-item-sugerencia' . ($index === 0 ? ' active' : '') . '"'
                . ' data-registro_id="' . (int)$turn->id . '"'
                . ' data-primer_item="' . ($index === 0 ? 1 : 0) . '"'
                . ' data-ultimo_item="' . ($index === $last ? 1 : 0) . '"'
                . ' data-accion="na">' . e($label) . '</a>';
        }
        if (empty($turns)) {
            $html .= '<span class="list-group-item">No se encontraron turnos válidos.</span>';
        }
        return $html . '</div>';
    }
}
