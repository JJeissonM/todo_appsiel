<?php

namespace App\Core\Services;

use App\Core\TurnoConfiguracion;
use App\Core\TurnoOperativo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class TurnoFormService
{
    protected $modeResolver;

    public function __construct(TurnoModeResolver $modeResolver)
    {
        $this->modeResolver = $modeResolver;
    }

    public function decorate($modelo, $registro, $accion, array $campos)
    {
        if (is_null($modelo) || !class_exists($modelo->name_space)) {
            return $campos;
        }

        $instance = app($modelo->name_space);
        if (!method_exists($instance, 'getTurnoModuleName')
            || !Schema::hasTable($instance->getTable())
            || !Schema::hasColumn($instance->getTable(), 'turno_operativo_id')) {
            return $campos;
        }

        $empresaId = $this->companyId($registro);
        if ($empresaId <= 0) {
            return $campos;
        }

        $manualModels = (array)config('turnos.manual_assignment_models', array());
        $module = isset($manualModels[$modelo->name_space])
            ? $manualModels[$modelo->name_space]
            : $instance->getTurnoModuleName();
        $persistedTurnId = is_object($registro) ? (int)$registro->getAttribute('turno_operativo_id') : 0;
        $isEdit = $accion === 'edit';
        $allowsManualCreate = isset($manualModels[$modelo->name_space]);
        $selectionLocked = !$isEdit && $this->selectionLockedForCurrentUser();

        if (!$isEdit && (!$allowsManualCreate || !$this->hasTurnosScope($empresaId, $module))) {
            return $campos;
        }
        if ($isEdit && $persistedTurnId <= 0 && !$allowsManualCreate) {
            return $campos;
        }

        $options = $this->options($empresaId, $module, $persistedTurnId, $isEdit);
        if ($selectionLocked) {
            $options = $this->lockedOptionsForRequest($options, $empresaId, $module);
        }
        $value = $persistedTurnId > 0 ? $persistedTurnId : $this->singleOptionValue($options);
        $field = array(
            'tipo' => 'select',
            'name' => 'turno_operativo_id',
            'descripcion' => ($isEdit || $selectionLocked)
                ? 'Turno operativo (asignado automáticamente)'
                : 'Turno operativo',
            'opciones' => $options,
            'value' => $value,
            'atributos' => ($isEdit || $selectionLocked)
                ? array('disabled' => 'disabled', 'class' => 'form-control', 'data-turno-locked' => '1')
                : array('class' => 'form-control combobox', 'data-turno-module' => $module),
            'definicion' => ($isEdit || $selectionLocked)
                ? 'El turno identifica el hecho operativo original y no puede reasignarse desde la edición.'
                : 'Obligatorio cuando el contexto seleccionado opera en modo TURNOS. Sólo se muestran turnos abiertos válidos para este módulo.',
            'requerido' => 0,
            'editable' => ($isEdit || $selectionLocked) ? 0 : 1,
            'unico' => 0,
        );

        foreach ($campos as $key => $campo) {
            if (isset($campo['name']) && $campo['name'] === 'turno_operativo_id') {
                $campos[$key] = array_merge($campo, $field);
                return $campos;
            }
        }
        $campos[] = $field;
        return $campos;
    }

    protected function selectionLockedForCurrentUser()
    {
        $user = Auth::user();
        if (is_null($user)) {
            return false;
        }
        foreach ((array)config('turnos.turn_selection_locked_roles', array()) as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    protected function lockedOptionsForRequest(array $options, $empresaId, $module)
    {
        $pdvId = (int)request()->input('pdv_id');
        if ($pdvId > 0) {
            $turno = TurnoOperativo::where('core_empresa_id', (int)$empresaId)
                ->where('contexto_tipo', 'pdv')
                ->where('contexto_id', $pdvId)
                ->abiertos()
                ->orderBy('id', 'DESC')
                ->first();
            if (!is_null($turno) && $this->modeResolver->enabled($empresaId, $module, 'pdv', $pdvId)) {
                return array($turno->id => $this->optionLabel($turno));
            }
            return array();
        }

        unset($options['']);
        return $options;
    }

    protected function companyId($registro)
    {
        if (is_object($registro)) {
            $id = (int)($registro->getAttribute('core_empresa_id') ?: $registro->getAttribute('empresa_id'));
            if ($id > 0) {
                return $id;
            }
        }
        return Auth::check() ? (int)Auth::user()->empresa_id : 0;
    }

    protected function hasTurnosScope($empresaId, $module)
    {
        if (!Schema::hasTable('core_turno_configuraciones')) {
            return false;
        }
        return TurnoConfiguracion::where('core_empresa_id', $empresaId)
            ->where('modo', TurnoConfiguracion::MODO_TURNOS)
            ->whereIn('modulo', array($module, '*'))
            ->exists();
    }

    protected function options($empresaId, $module, $persistedTurnId, $isEdit)
    {
        $options = array('' => 'Seleccione un turno operativo');
        $query = TurnoOperativo::where('core_empresa_id', $empresaId);
        if ($isEdit && $persistedTurnId > 0) {
            $query->where('id', $persistedTurnId);
        } else {
            $query->abiertos();
        }

        foreach ($query->orderBy('abierto_en', 'DESC')->get() as $turno) {
            if (!$isEdit && !$this->modeResolver->enabled(
                $empresaId, $module, $turno->contexto_tipo, (int)$turno->contexto_id
            )) {
                continue;
            }
            $options[$turno->id] = $this->optionLabel($turno);
        }
        return $options;
    }

    protected function optionLabel(TurnoOperativo $turno)
    {
        return $turno->codigo . ' | ' . $turno->fecha_operativa . ' | '
            . $turno->contexto_tipo . ' ' . $turno->contexto_id . ' | ' . $turno->estado;
    }

    protected function singleOptionValue(array $options)
    {
        $values = array_filter(array_keys($options), function ($value) {
            return (int)$value > 0;
        });
        return count($values) === 1 ? (int)reset($values) : null;
    }
}
