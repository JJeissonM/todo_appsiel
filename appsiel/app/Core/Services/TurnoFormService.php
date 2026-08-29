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
        $allowsClosedAdjustment = !$isEdit && !$selectionLocked && $this->canRegisterAdjustment();

        if (!$isEdit && (!$allowsManualCreate || !$this->hasTurnosScope($empresaId, $module))) {
            return $campos;
        }
        if ($isEdit && $persistedTurnId <= 0 && !$allowsManualCreate) {
            return $campos;
        }

        $options = array();
        $value = null;
        if ($isEdit) {
            $options = $this->options($empresaId, $module, $persistedTurnId, true);
            $value = $persistedTurnId;
        } elseif ($selectionLocked) {
            $options = $this->lockedOptionsForRequest($empresaId, $module);
            $value = $this->singleOptionValue($options);
        } else {
            $defaultTurn = $this->defaultOpenTurnForRequest($empresaId, $module);
            $value = is_null($defaultTurn)
                ? array('', '')
                : array($this->optionLabel($defaultTurn), (int)$defaultTurn->id);
        }

        $isAjaxSelector = !$isEdit && !$selectionLocked;
        $field = array(
            'tipo' => $isAjaxSelector ? 'input_lista_sugerencias' : 'select',
            'name' => 'turno_operativo_id',
            'descripcion' => ($isEdit || $selectionLocked)
                ? 'Turno operativo (asignado automáticamente)'
                : 'Turno operativo',
            'opciones' => $options,
            'value' => $value,
            'atributos' => ($isEdit || $selectionLocked)
                ? array('disabled' => 'disabled', 'class' => 'form-control', 'data-turno-locked' => '1')
                : array(
                    'class' => 'form-control text_input_sugerencias turno-operativo-ajax',
                    'data-url_busqueda' => url('turnos/operativos/sugerencias') . '?modulo=' . rawurlencode($module),
                    'data-ajax-fields' => 'pdv_id',
                    'data-turno-module' => $module,
                    'data-selected-label' => is_null($defaultTurn) ? '' : $this->optionLabel($defaultTurn),
                ),
            'definicion' => ($isEdit || $selectionLocked)
                ? 'El turno identifica el hecho operativo original y no puede reasignarse desde la edición.'
                : 'Busque por código, PDV o fecha. La consulta se realiza bajo demanda y sólo devuelve turnos válidos para la empresa, módulo, contexto y permisos actuales.',
            'requerido' => 0,
            'editable' => ($isEdit || $selectionLocked) ? 0 : 1,
            'unico' => 0,
        );

        $fieldReplaced = false;
        foreach ($campos as $key => $campo) {
            if (isset($campo['name']) && $campo['name'] === 'turno_operativo_id') {
                $campos[$key] = array_merge($campo, $field);
                $fieldReplaced = true;
                break;
            }
        }
        if (!$fieldReplaced) {
            $campos[] = $field;
        }

        if ($allowsClosedAdjustment) {
            $campos[] = $this->adjustmentReasonField();
        }
        return $campos;
    }

    protected function canRegisterAdjustment()
    {
        return Auth::check() && Auth::user()->can('turnos.ajustes.registrar');
    }

    protected function adjustmentReasonField()
    {
        return array(
            'tipo' => 'bsTextArea',
            'name' => 'turno_ajuste_motivo',
            'descripcion' => 'Motivo de ajuste sobre turno cerrado',
            'opciones' => array(),
            'value' => null,
            'atributos' => array('class' => 'form-control', 'rows' => 2),
            'definicion' => 'Obligatorio únicamente cuando se selecciona un turno CERRADO o AUDITADO.',
            'requerido' => 0,
            'editable' => 1,
            'unico' => 0,
        );
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

    protected function lockedOptionsForRequest($empresaId, $module)
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

        return array();
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

    protected function defaultOpenTurnForRequest($empresaId, $module)
    {
        $pdvId = (int)request()->input('pdv_id');
        if ($pdvId <= 0 || !$this->modeResolver->enabled($empresaId, $module, 'pdv', $pdvId)) {
            return null;
        }

        return TurnoOperativo::where('core_empresa_id', (int)$empresaId)
            ->where('contexto_tipo', 'pdv')
            ->where('contexto_id', $pdvId)
            ->abiertos()
            ->orderBy('id', 'DESC')
            ->first();
    }

    protected function optionLabel(TurnoOperativo $turno)
    {
        $contextLabel = $turno->contexto_tipo . ' ' . $turno->contexto_id;
        if ($turno->contexto_tipo === 'pdv') {
            $pdvName = \DB::table('vtas_pos_puntos_de_ventas')
                ->where('core_empresa_id', (int)$turno->core_empresa_id)
                ->where('id', (int)$turno->contexto_id)
                ->value('descripcion');
            if (!empty($pdvName)) {
                $contextLabel = 'PDV ' . $turno->contexto_id . ' - ' . $pdvName;
            }
        }
        return $turno->codigo . ' | ' . $turno->fecha_operativa . ' | '
            . $contextLabel . ' | ' . $turno->estado;
    }

    protected function singleOptionValue(array $options)
    {
        $values = array_filter(array_keys($options), function ($value) {
            return (int)$value > 0;
        });
        return count($values) === 1 ? (int)reset($values) : null;
    }
}
