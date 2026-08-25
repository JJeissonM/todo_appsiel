<?php

namespace App\Core\Services;

use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\TurnoConfiguracion;
use App\Core\TurnoOperativo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TurnoConfigurationService
{
    protected $resolver;

    public function __construct(TurnoModeResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function configure(array $data)
    {
        $shapeErrors = $this->validateShape($data);
        if (!empty($shapeErrors)) {
            throw new TurnoIntegrityException(implode(' ', $shapeErrors));
        }
        $service = $this;
        $result = DB::transaction(function () use ($data, $service) {
            $service->lockCompany((int)$data['core_empresa_id']);
            $analysis = $service->analyzeCandidate($data, true);
            if (!empty($analysis['errors'])) {
                throw new TurnoIntegrityException(implode(' ', $analysis['errors']));
            }
            $configuration = TurnoConfiguracion::where('core_empresa_id', (int)$data['core_empresa_id'])
                ->where('modulo', $data['modulo'])
                ->where('contexto_tipo', $data['contexto_tipo'])
                ->where('contexto_id', (int)$data['contexto_id'])
                ->lockForUpdate()
                ->first();
            if (is_null($configuration)) {
                $configuration = new TurnoConfiguracion();
            }
            $configuration->fill($data);
            $configuration->save();
            $service->resolver->clearCache();
            return array('configuration' => $configuration, 'warnings' => $analysis['warnings']);
        });

        return $result;
    }

    public function validateShape(array $data)
    {
        $errors = array();
        if ((int)(isset($data['core_empresa_id']) ? $data['core_empresa_id'] : 0) <= 0) {
            $errors[] = 'La configuración debe indicar una empresa válida.';
        } elseif (Schema::hasTable('core_empresas') && !DB::table('core_empresas')->where('id', (int)$data['core_empresa_id'])->exists()) {
            $errors[] = 'La empresa indicada no existe.';
        }
        if (!in_array(isset($data['modo']) ? $data['modo'] : null, array(TurnoConfiguracion::MODO_TRADICIONAL, TurnoConfiguracion::MODO_TURNOS), true)) {
            $errors[] = 'El modo debe ser TRADICIONAL o TURNOS.';
        }
        $module = isset($data['modulo']) ? $data['modulo'] : '';
        $modules = (array)config('turnos.modules', array());
        if ($module !== '*' && !array_key_exists($module, $modules)) {
            $errors[] = 'El módulo ' . $module . ' no está declarado en config/turnos.php.';
        }
        $contextType = isset($data['contexto_tipo']) ? trim((string)$data['contexto_tipo']) : '';
        $contextId = (int)(isset($data['contexto_id']) ? $data['contexto_id'] : 0);
        if ($contextType === '' || ($contextType === '*' && $contextId !== 0) || ($contextType !== '*' && $contextId <= 0)) {
            $errors[] = 'El tipo e ID del contexto operativo no forman un alcance válido.';
        }
        return $errors;
    }

    public function analyzeCandidate(array $data, $lockTurns = false)
    {
        $errors = $this->validateShape($data);
        $warnings = array();
        if (!empty($errors)) {
            return array('errors' => array_values(array_unique($errors)), 'warnings' => $warnings);
        }
        $module = isset($data['modulo']) ? $data['modulo'] : '';
        $mode = isset($data['modo']) ? $data['modo'] : '';
        $modules = (array)config('turnos.modules', array());

        if ($mode === TurnoConfiguracion::MODO_TURNOS && $module !== '*' && isset($modules[$module]) && empty($modules[$module]['integrated'])) {
            $errors[] = 'El módulo ' . $module . ' todavía no persiste turnos y no puede activarse en modo TURNOS.';
        }

        $errors = array_merge($errors, $this->activeTurnConflicts($data, $lockTurns));
        $errors = array_merge($errors, $this->legacyOpenConflicts($data, null));

        foreach ((array)config('turnos.operation_groups', array()) as $groupName => $group) {
            if ($module !== '*' && !in_array($module, $group['modules'], true)) {
                continue;
            }
            $modes = array();
            foreach ($group['modules'] as $participant) {
                $modes[$participant] = $this->effectiveModeWithCandidate($participant, $data);
            }
            if (count(array_unique($modes)) > 1) {
                $message = 'El grupo ' . $groupName . ' quedará con modos mixtos: ' . $this->formatModes($modes) . '. Los derivados integrados conservarán el turno, pero debe validarse que los módulos tradicionales sean realmente independientes.';
                if (!empty($group['enforce_uniform_mode'])) {
                    $errors[] = $message;
                } else {
                    $warnings[] = $message;
                }
            }
        }

        return array('errors' => array_values(array_unique($errors)), 'warnings' => array_values(array_unique($warnings)));
    }

    public function assertCanDelete(TurnoConfiguracion $configuration, $lockTurns = false)
    {
        $errors = array_merge(
            $this->activeTurnConflicts(null, $lockTurns, $configuration),
            $this->legacyOpenConflicts(null, $configuration)
        );
        if (!empty($errors)) {
            throw new TurnoIntegrityException(implode(' ', $errors));
        }
    }

    protected function legacyOpenConflicts(array $candidate = null, TurnoConfiguracion $ignored = null)
    {
        $empresaId = !is_null($candidate)
            ? (int)$candidate['core_empresa_id']
            : (is_null($ignored) ? 0 : (int)$ignored->core_empresa_id);
        if ($empresaId <= 0 || !Schema::hasTable('vtas_pos_puntos_de_ventas')) {
            return array();
        }

        $errors = array();
        $pdvs = DB::table('vtas_pos_puntos_de_ventas')
            ->where('core_empresa_id', $empresaId)
            ->where('estado', 'Abierto')
            ->orderBy('id')
            ->get();
        foreach ($pdvs as $pdv) {
            $hasCoreTurn = TurnoOperativo::where('core_empresa_id', $empresaId)
                ->where('contexto_tipo', 'pdv')->where('contexto_id', $pdv->id)->abiertos()->exists();
            if ($hasCoreTurn) {
                continue;
            }
            foreach ($this->integratedModules() as $module) {
                $current = $this->effectiveModeAt($empresaId, $module, 'pdv', $pdv->id);
                $proposed = $this->effectiveModeAt(
                    $empresaId, $module, 'pdv', $pdv->id, $candidate,
                    is_null($ignored) ? null : $ignored->id
                );
                if ($current !== $proposed) {
                    $errors[] = 'El PDV ' . $pdv->id . ' figura ABIERTO bajo el modelo tradicional e impide cambiar el modo efectivo de '
                        . $module . ' de ' . $current . ' a ' . $proposed . '. Cierre primero la apertura tradicional.';
                }
            }
        }
        return array_values(array_unique($errors));
    }

    protected function activeTurnConflicts(array $candidate = null, $lockTurns = false, TurnoConfiguracion $ignored = null)
    {
        $empresaId = !is_null($candidate)
            ? (int)$candidate['core_empresa_id']
            : (is_null($ignored) ? 0 : (int)$ignored->core_empresa_id);
        if ($empresaId <= 0) {
            return array();
        }

        $turnQuery = TurnoOperativo::where('core_empresa_id', $empresaId)->abiertos()->orderBy('id');
        if ($lockTurns) {
            $turnQuery->lockForUpdate();
        }
        $turnos = $turnQuery->get();
        if ($turnos->isEmpty()) {
            return array();
        }

        $errors = array();
        foreach ($turnos as $turno) {
            foreach ($this->integratedModules() as $module) {
                $current = $this->effectiveModeAt($empresaId, $module, $turno->contexto_tipo, $turno->contexto_id);
                $proposed = $this->effectiveModeAt(
                    $empresaId,
                    $module,
                    $turno->contexto_tipo,
                    $turno->contexto_id,
                    $candidate,
                    is_null($ignored) ? null : $ignored->id
                );
                if ($current !== $proposed) {
                    $errors[] = 'El turno ' . $turno->id . ' (' . $turno->codigo . ') está ABIERTO para '
                        . $turno->contexto_tipo . ' ' . $turno->contexto_id . ' e impide cambiar el modo efectivo de '
                        . $module . ' de ' . $current . ' a ' . $proposed . '.';
                }
            }
        }
        return array_values(array_unique($errors));
    }

    protected function integratedModules()
    {
        $result = array();
        foreach ((array)config('turnos.modules', array()) as $module => $settings) {
            if (!empty($settings['integrated'])) {
                $result[] = $module;
            }
        }
        return $result;
    }

    protected function effectiveModeAt($empresaId, $module, $contextType, $contextId, array $candidate = null, $ignoredId = null)
    {
        $scopes = array(
            array($module, $contextType, (int)$contextId),
            array('*', $contextType, (int)$contextId),
            array($module, '*', 0),
            array('*', '*', 0),
        );
        foreach ($scopes as $scope) {
            if (!is_null($candidate)
                && $candidate['modulo'] === $scope[0]
                && $candidate['contexto_tipo'] === $scope[1]
                && (int)$candidate['contexto_id'] === (int)$scope[2]) {
                return $candidate['modo'];
            }
            $query = TurnoConfiguracion::where('core_empresa_id', $empresaId)
                ->where('modulo', $scope[0])
                ->where('contexto_tipo', $scope[1])
                ->where('contexto_id', $scope[2]);
            if (!is_null($ignoredId)) {
                $query->where('id', '<>', (int)$ignoredId);
            }
            $configuration = $query->orderBy('id', 'DESC')->first();
            if (!is_null($configuration)) {
                return $configuration->modo;
            }
        }
        return TurnoConfiguracion::MODO_TRADICIONAL;
    }

    public function lockCompany($empresaId)
    {
        DB::table('core_empresas')->where('id', (int)$empresaId)->lockForUpdate()->first();
    }

    protected function effectiveModeWithCandidate($module, array $candidate)
    {
        $scopes = array(
            array($module, $candidate['contexto_tipo'], (int)$candidate['contexto_id']),
            array('*', $candidate['contexto_tipo'], (int)$candidate['contexto_id']),
            array($module, '*', 0),
            array('*', '*', 0),
        );
        foreach ($scopes as $scope) {
            if ($candidate['modulo'] === $scope[0] && $candidate['contexto_tipo'] === $scope[1] && (int)$candidate['contexto_id'] === $scope[2]) {
                return $candidate['modo'];
            }
            $configuration = TurnoConfiguracion::where('core_empresa_id', (int)$candidate['core_empresa_id'])
                ->where('modulo', $scope[0])
                ->where('contexto_tipo', $scope[1])
                ->where('contexto_id', $scope[2])
                ->orderBy('id', 'DESC')
                ->first();
            if (!is_null($configuration)) {
                return $configuration->modo;
            }
        }
        return TurnoConfiguracion::MODO_TRADICIONAL;
    }

    protected function formatModes(array $modes)
    {
        $parts = array();
        foreach ($modes as $module => $mode) {
            $parts[] = $module . '=' . $mode;
        }
        return implode(', ', $parts);
    }
}
