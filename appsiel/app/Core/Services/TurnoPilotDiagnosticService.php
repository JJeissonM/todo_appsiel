<?php

namespace App\Core\Services;

use App\Core\Empresa;
use App\Core\TurnoOperativo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TurnoPilotDiagnosticService
{
    protected $modeResolver;

    public function __construct(TurnoModeResolver $modeResolver)
    {
        $this->modeResolver = $modeResolver;
    }

    public function diagnose($empresaId, $contextType, $contextId, $days = 7)
    {
        $empresaId = (int)$empresaId;
        $contextType = trim((string)$contextType);
        $contextId = (int)$contextId;
        $days = max(1, min(90, (int)$days));
        $empresa = Empresa::find($empresaId);

        $result = array(
            'empresa_id' => $empresaId,
            'empresa' => is_null($empresa) ? null : $empresa->descripcion,
            'contexto_tipo' => $contextType,
            'contexto_id' => $contextId,
            'dias_revisados' => $days,
            'modules' => $this->moduleModes($empresaId, $contextType, $contextId),
            'groups' => $this->groupModes($empresaId, $contextType, $contextId),
            'open_turn' => $this->openTurn($empresaId, $contextType, $contextId),
            'recent_null_fk' => $this->recentNullForeignKeys($empresaId, $contextType, $contextId, $days),
            'legacy_queries' => $this->legacyQueries(),
            'warnings' => array(),
        );

        if (is_null($empresa)) {
            $result['warnings'][] = 'La empresa indicada no existe.';
        }
        if ($contextType === '' || $contextId <= 0) {
            $result['warnings'][] = 'El contexto no es válido.';
        }
        foreach ($result['groups'] as $group) {
            if ($group['mixed']) {
                $result['warnings'][] = 'El grupo ' . $group['name'] . ' tiene modos mixtos.';
            }
        }
        foreach ($result['recent_null_fk'] as $source) {
            if ($source['count'] > 0) {
                $result['warnings'][] = $source['label'] . ' contiene ' . $source['count'] . ' registros recientes sin turno (' . $source['scope'] . ').';
            }
        }
        if ($contextType === 'pdv' && Schema::hasTable('vtas_pos_puntos_de_ventas')) {
            $pdv = DB::table('vtas_pos_puntos_de_ventas')->where('id', $contextId)->where('core_empresa_id', $empresaId)->first();
            if (is_null($pdv)) {
                $result['warnings'][] = 'El PDV no existe o no pertenece a la empresa.';
            } elseif (isset($pdv->estado) && $pdv->estado === 'Abierto' && is_null($result['open_turn'])) {
                $result['warnings'][] = 'El PDV figura abierto bajo el modelo tradicional y no tiene un turno operativo asociado. Debe cerrarse antes de activar TURNOS.';
            }
        }

        $result['ready'] = !is_null($empresa) && empty($result['warnings']);
        return $result;
    }

    protected function moduleModes($empresaId, $contextType, $contextId)
    {
        $result = array();
        foreach ((array)config('turnos.modules', array()) as $module => $settings) {
            $result[$module] = array(
                'integrated' => !empty($settings['integrated']),
                'mode' => $this->modeResolver->enabled($empresaId, $module, $contextType, $contextId) ? 'TURNOS' : 'TRADICIONAL',
            );
        }
        return $result;
    }

    protected function groupModes($empresaId, $contextType, $contextId)
    {
        $result = array();
        foreach ((array)config('turnos.operation_groups', array()) as $name => $group) {
            $modes = array();
            foreach ($group['modules'] as $module) {
                $modes[$module] = $this->modeResolver->enabled($empresaId, $module, $contextType, $contextId) ? 'TURNOS' : 'TRADICIONAL';
            }
            $result[] = array('name' => $name, 'modes' => $modes, 'mixed' => count(array_unique($modes)) > 1);
        }
        return $result;
    }

    protected function openTurn($empresaId, $contextType, $contextId)
    {
        $turno = TurnoOperativo::where('core_empresa_id', $empresaId)
            ->where('contexto_tipo', $contextType)
            ->where('contexto_id', $contextId)
            ->abiertos()
            ->orderBy('id', 'DESC')
            ->first();
        if (is_null($turno)) {
            return null;
        }
        return array(
            'id' => $turno->id,
            'codigo' => $turno->codigo,
            'fecha_operativa' => $turno->fecha_operativa,
            'estado' => $turno->estado,
            'abierto_en' => (string)$turno->abierto_en,
            'abierto_por' => $turno->abierto_por,
        );
    }

    protected function recentNullForeignKeys($empresaId, $contextType, $contextId, $days)
    {
        $since = date('Y-m-d H:i:s', strtotime('-' . (int)$days . ' days'));
        $result = array();
        foreach ((array)config('turnos.diagnostic_sources', array()) as $source) {
            if (!Schema::hasTable($source['table']) || !Schema::hasColumn($source['table'], 'turno_operativo_id')) {
                continue;
            }
            $query = DB::table($source['table'])
                ->where($source['company_column'], $empresaId)
                ->whereNull('turno_operativo_id');
            if (Schema::hasColumn($source['table'], 'created_at')) {
                $query->where('created_at', '>=', $since);
            }
            $scope = 'EMPRESA';
            if (isset($source['context_type'], $source['context_column']) && $source['context_type'] === $contextType) {
                $query->where($source['context_column'], $contextId);
                $scope = strtoupper($contextType) . ':' . $contextId;
            }
            $sampleQuery = clone $query;
            $sampleIds = $sampleQuery->orderBy('id', 'DESC')->limit(5)->pluck('id');
            if (is_object($sampleIds) && method_exists($sampleIds, 'toArray')) {
                $sampleIds = $sampleIds->toArray();
            }
            $result[] = array(
                'module' => $source['module'],
                'label' => $source['label'],
                'table' => $source['table'],
                'scope' => $scope,
                'count' => (int)$query->count(),
                'sample_ids' => array_values((array)$sampleIds),
            );
        }
        return $result;
    }

    protected function legacyQueries()
    {
        return array(
            'Reportes especializados de Tesorería que filtran históricos por fecha/hora.',
            'CashRegisterShiftService::getDayRange en interfaces TRADICIONALES.',
            'Parche TesoMovimiento::sincronizarCreatedAtConUltimoCierre, sólo TRADICIONAL.',
            'Reportes históricos de aperturas/cierres POS basados en fecha de documento.',
        );
    }
}
