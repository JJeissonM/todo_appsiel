<?php

namespace App\Core\Services;

use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\TurnoConfiguracion;
use Illuminate\Support\Facades\DB;

class TurnoConfigurationService
{
    protected $resolver;

    public function __construct(TurnoModeResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function configure(array $data)
    {
        $analysis = $this->analyzeCandidate($data);
        if (!empty($analysis['errors'])) {
            throw new TurnoIntegrityException(implode(' ', $analysis['errors']));
        }

        $service = $this;
        $configuration = DB::transaction(function () use ($data, $service) {
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
            return $configuration;
        });

        return array('configuration' => $configuration, 'warnings' => $analysis['warnings']);
    }

    public function validateShape(array $data)
    {
        $errors = array();
        if ((int)(isset($data['core_empresa_id']) ? $data['core_empresa_id'] : 0) <= 0) {
            $errors[] = 'La configuración debe indicar una empresa válida.';
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

    public function analyzeCandidate(array $data)
    {
        $errors = $this->validateShape($data);
        $warnings = array();
        $module = isset($data['modulo']) ? $data['modulo'] : '';
        $mode = isset($data['modo']) ? $data['modo'] : '';
        $modules = (array)config('turnos.modules', array());

        if ($mode === TurnoConfiguracion::MODO_TURNOS && $module !== '*' && isset($modules[$module]) && empty($modules[$module]['integrated'])) {
            $errors[] = 'El módulo ' . $module . ' todavía no persiste turnos y no puede activarse en modo TURNOS.';
        }

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
