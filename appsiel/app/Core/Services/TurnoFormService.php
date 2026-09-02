<?php

namespace App\Core\Services;

use App\Core\TurnoConfiguracion;
use App\Core\TurnoOperativo;
use App\Inventarios\Services\InventoryPhysicalPdvShiftService;
use App\Tesoreria\TesoDocEncabezadoTraslado;
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
        $isInventoryPhysical = $modelo->name_space === 'App\\Inventarios\\InvFisico';
        $isCashTransfer = $modelo->name_space === TesoDocEncabezadoTraslado::class;
        $selectionLocked = !$isEdit && $this->selectionLockedForCurrentUser();
        $administrativeOptional = !$isEdit
            && !$selectionLocked
            && config('turnos.simple_company_mode', false);
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
            $closedCashierOperation = $isInventoryPhysical
                ? InventoryPhysicalPdvShiftService::CLOSING_CONTROL_OPERATION
                : ($isCashTransfer ? TesoDocEncabezadoTraslado::POST_CLOSING_OPERATION : null);
            $options = $this->lockedOptionsForRequest($empresaId, $module, $closedCashierOperation);
            $value = $this->singleOptionValue($options);
        } elseif ($administrativeOptional) {
            // El administrador decide explícitamente si la transacción pertenece
            // o no a un turno; no se preselecciona uno de forma silenciosa.
            $defaultTurn = null;
            $value = array('', '');
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
                : ($administrativeOptional
                    ? 'Opcional para usuarios administrativos. Busque por código o fecha únicamente cuando la transacción pertenezca a un turno.'
                    : 'Busque por código, PDV o fecha para asociar la operación al turno vigente.'),
            'requerido' => 0,
            'editable' => ($isEdit || $selectionLocked) ? 0 : 1,
            'unico' => 0,
        );
        if (!$isEdit) {
            $field['atributos']['data-turno-validation'] = '1';
            $field['atributos']['data-turno-validation-url'] = url('turnos/operativos/validar-seleccion');
            $field['atributos']['data-turno-module'] = $module;
            if ($isInventoryPhysical && $selectionLocked) {
                $field['atributos']['data-turno-operation'] = InventoryPhysicalPdvShiftService::CLOSING_CONTROL_OPERATION;
                $field['definicion'] = 'Si el cajero ya cerró su turno, se asigna su último turno cerrado para documentar el control físico de entrega.';
            } elseif ($isCashTransfer && $selectionLocked) {
                $field['atributos']['data-turno-operation'] = TesoDocEncabezadoTraslado::POST_CLOSING_OPERATION;
                $field['definicion'] = 'El traslado de efectivo se asocia automáticamente al último turno cerrado por el cajero.';
            }
        }

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

    /**
     * Fija en un formulario de creación el turno de un documento origen.
     *
     * Se usa en operaciones derivadas que no constituyen un nuevo hecho
     * operativo (por ejemplo, el ajuste de un inventario físico). El campo se
     * muestra para trazabilidad, pero no puede ser reasignado por el usuario.
     */
    public function lockToOrigin($modelo, $origin, array $campos)
    {
        if (is_null($modelo) || !is_object($origin) || !class_exists($modelo->name_space)) {
            return $campos;
        }

        $turnId = (int)$origin->getAttribute('turno_operativo_id');
        $companyId = $this->companyId($origin);
        if ($turnId <= 0 || $companyId <= 0) {
            return $campos;
        }

        $instance = app($modelo->name_space);
        if (!method_exists($instance, 'getTurnoModuleName')) {
            return $campos;
        }
        $manualModels = (array)config('turnos.manual_assignment_models', array());
        $module = isset($manualModels[$modelo->name_space])
            ? $manualModels[$modelo->name_space]
            : $instance->getTurnoModuleName();
        $options = $this->options($companyId, $module, $turnId, true);

        foreach ($campos as $key => $campo) {
            if (!isset($campo['name']) || $campo['name'] !== 'turno_operativo_id') {
                continue;
            }

            $campos[$key] = array_merge($campo, array(
                'tipo' => 'select',
                'descripcion' => 'Turno operativo (heredado del inventario físico)',
                'opciones' => $options,
                'value' => $turnId,
                'atributos' => array(
                    'disabled' => 'disabled',
                    'class' => 'form-control',
                    'data-turno-locked' => '1',
                    'data-turno-validation' => '1',
                    'data-turno-validation-url' => url('turnos/operativos/validar-seleccion'),
                    'data-turno-module' => $module,
                ),
                'definicion' => 'El ajuste conserva el turno del Inventario Físico origen y no puede reasignarse.',
                'requerido' => 0,
                'editable' => 0,
                'unico' => 0,
            ));
            break;
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

    protected function lockedOptionsForRequest($empresaId, $module, $closedCashierOperation = null)
    {
        $userId = Auth::check() ? (int)Auth::user()->id : 0;
        if ($userId <= 0) {
            return array();
        }

        $pdvId = (int)request()->input('pdv_id');
        // El traslado documenta la entrega del efectivo del turno que acaba de
        // finalizar. Debe conservar ese turno incluso si posteriormente el
        // cajero abrió uno nuevo.
        if ($closedCashierOperation === TesoDocEncabezadoTraslado::POST_CLOSING_OPERATION) {
            return $this->lastClosedCashierOption($empresaId, $module, $userId, $pdvId);
        }

        if ($pdvId > 0 && !config('turnos.simple_company_mode', false)) {
            $turno = TurnoOperativo::where('core_empresa_id', (int)$empresaId)
                ->where('contexto_tipo', 'pdv')
                ->where('contexto_id', $pdvId)
                ->where('abierto_por', $userId)
                ->abiertos()
                ->orderBy('id', 'DESC')
                ->first();
            if (!is_null($turno) && $this->modeResolver->enabled($empresaId, $module, 'pdv', $pdvId)) {
                return array($turno->id => $this->optionLabel($turno));
            }
            return !is_null($closedCashierOperation)
                ? $this->lastClosedCashierOption($empresaId, $module, $userId, $pdvId)
                : array();
        }

        // Algunos formularios de transacciones (por ejemplo, recaudos de CxC)
        // no exponen un PDV. En esos casos el contexto inequívoco del cajero es
        // la apertura vigente que quedó registrada a su nombre. Se recorren las
        // aperturas más recientes porque un mismo usuario puede operar contextos
        // distintos, pero sólo se acepta uno habilitado para el módulo actual.
        $turnos = TurnoOperativo::where('core_empresa_id', (int)$empresaId)
            ->where('abierto_por', $userId)
            ->abiertos()
            ->orderBy('abierto_en', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        foreach ($turnos as $turno) {
            if ($this->modeResolver->enabled(
                $empresaId,
                $module,
                $turno->contexto_tipo,
                (int)$turno->contexto_id
            )) {
                return array($turno->id => $this->optionLabel($turno));
            }
        }

        return !is_null($closedCashierOperation)
            ? $this->lastClosedCashierOption($empresaId, $module, $userId)
            : array();
    }

    protected function lastClosedCashierOption($empresaId, $module, $userId, $pdvId = null)
    {
        $turno = app(InventoryPhysicalPdvShiftService::class)
            ->lastClosedTurnForCashier($empresaId, $userId, $pdvId);
        if (is_null($turno) || !$this->modeResolver->enabled(
            $empresaId,
            $module,
            $turno->contexto_tipo,
            (int)$turno->contexto_id
        )) {
            return array();
        }

        return array($turno->id => $this->optionLabel($turno));
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
        if ($pdvId > 0) {
            if (!$this->modeResolver->enabled($empresaId, $module, 'pdv', $pdvId)) {
                return null;
            }

            return TurnoOperativo::where('core_empresa_id', (int)$empresaId)
                ->where('contexto_tipo', 'pdv')
                ->where('contexto_id', $pdvId)
                ->abiertos()
                ->orderBy('id', 'DESC')
                ->first();
        }

        // Inventarios y varios encabezados de Tesorería no tienen PDV en el
        // formulario. Si sólo existe un turno abierto habilitado para el módulo,
        // su identidad es inequívoca y puede preseleccionarse. Con dos o más se
        // conserva el selector manual para exigir el contexto explícito.
        $turnos = TurnoOperativo::where('core_empresa_id', (int)$empresaId)
            ->abiertos()
            ->orderBy('abierto_en', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->filter(function ($turno) use ($empresaId, $module) {
                return $this->modeResolver->enabled(
                    $empresaId,
                    $module,
                    $turno->contexto_tipo,
                    (int)$turno->contexto_id
                );
            });

        return $turnos->count() === 1 ? $turnos->first() : null;
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
