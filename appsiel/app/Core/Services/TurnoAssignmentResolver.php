<?php

namespace App\Core\Services;

use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Exceptions\TurnoRequiredException;
use App\Core\Exceptions\TurnoStateException;
use App\Core\TurnoOperativo;
use App\Inventarios\InvDocEncabezado;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TurnoAssignmentResolver
{
    protected $context;
    protected $manager;
    protected $modeResolver;
    protected $lastSource = 'UNRESOLVED';

    public function __construct(TurnoContext $context, TurnoManager $manager, TurnoModeResolver $modeResolver)
    {
        $this->context = $context;
        $this->manager = $manager;
        $this->modeResolver = $modeResolver;
    }

    public function assign(Model $model, $module, $contextId = null, $contextType = null)
    {
        $this->lastSource = 'UNRESOLVED';
        try {
            return $this->resolve($model, $module, $contextId, $contextType);
        } catch (\UnexpectedValueException $e) {
            $this->logFailure($model, $module, $contextId, $contextType, $e);
            throw $e;
        }
    }

    protected function resolve(Model $model, $module, $contextId = null, $contextType = null)
    {
        if (!Schema::hasColumn($model->getTable(), 'turno_operativo_id')) {
            $this->lastSource = 'TRADITIONAL';
            return null;
        }

        $empresaId = (int)$this->companyId($model);
        $descriptor = $this->contextDescriptor($model, $contextType, $contextId);
        $originTurn = $this->fromPersistedOrigin($model);

        $turno = $this->context->current();
        if (!is_null($turno)) {
            $this->lastSource = 'CONTEXT';
            $this->validateIdentity($turno, $empresaId, $descriptor);
            if (!empty($model->turno_operativo_id) && (int)$model->turno_operativo_id !== (int)$turno->id) {
                throw new TurnoIntegrityException('El turno explícito de la operación contradice el TurnoContext propagado.');
            }
            if (!$turno->estaAbierto() && !$this->context->allowsHistoricalTurn()) {
                throw new TurnoStateException('El turno propagado por el contexto ya no está abierto.');
            }
            return $model->turno_operativo_id = $turno->id;
        }

        if (!empty($model->turno_operativo_id)) {
            $this->lastSource = 'EXPLICIT_FK';
            $turno = TurnoOperativo::find((int)$model->turno_operativo_id);
            $this->validateIdentity($turno, $empresaId, $descriptor);
            if (!is_null($originTurn) && (int)$originTurn->id !== (int)$turno->id) {
                throw new TurnoIntegrityException('El turno explícito de la operación contradice el turno persistido en su documento origen.');
            }
            if ($turno->estaAbierto() || $this->isAuthorizedHistorical($turno, $originTurn) || $this->modelAllowsHistoricalAssignment($model)) {
                return $model->turno_operativo_id;
            }
            throw new TurnoStateException('No se puede asociar una operación normal a un turno cerrado, en auditoría o auditado.');
        }

        if (!is_null($originTurn)) {
            $this->validateIdentity($originTurn, $empresaId, $descriptor);
            return $model->turno_operativo_id = $originTurn->id;
        }

        if (!is_null($descriptor) && $this->manager->enabledForContext($empresaId, $module, $descriptor['type'], $descriptor['id'])) {
            $this->lastSource = 'OPEN_CONTEXT';
            $turno = $this->manager->requireCurrent($empresaId, $module, $descriptor['type'], $descriptor['id']);
            return $model->turno_operativo_id = $turno->id;
        }

        if (is_null($descriptor) && $this->modeResolver->enabledForModule($empresaId, $module)) {
            $this->lastSource = 'OPEN_CONTEXT';
            throw new TurnoRequiredException('El módulo ' . $module . ' opera en modo TURNOS. Debe propagar un turno o un contexto operativo válido antes de crear la operación.');
        }

        $this->lastSource = 'TRADITIONAL';
        return null;
    }

    public function lastResolutionSource()
    {
        return $this->lastSource;
    }

    public function recoverFromOrigin($originType, $originId)
    {
        if (!class_exists($originType) || !is_subclass_of($originType, Model::class)) {
            throw new TurnoIntegrityException('El tipo de operación origen no es válido para recuperar el turno.');
        }
        $origin = call_user_func(array($originType, 'find'), (int)$originId);
        if (is_null($origin) || empty($origin->turno_operativo_id)) {
            throw new TurnoIntegrityException('La operación origen no existe o no conserva un turno operativo.');
        }
        $turno = TurnoOperativo::find((int)$origin->turno_operativo_id);
        if (is_null($turno) || (int)$turno->core_empresa_id !== (int)$this->companyId($origin)) {
            throw new TurnoIntegrityException('La relación persistida entre la operación origen y el turno es inconsistente.');
        }
        return $turno;
    }

    protected function validateIdentity($turno, $empresaId, $descriptor)
    {
        if (is_null($turno)) {
            throw new TurnoIntegrityException('El turno operativo indicado no existe.');
        }
        if ((int)$turno->core_empresa_id !== (int)$empresaId) {
            throw new TurnoIntegrityException('El turno operativo pertenece a otra empresa.');
        }
        if (!is_null($descriptor) && ($turno->contexto_tipo !== $descriptor['type'] || (int)$turno->contexto_id !== (int)$descriptor['id'])) {
            throw new TurnoIntegrityException('El turno operativo pertenece a otro contexto operativo.');
        }
    }

    protected function isAuthorizedHistorical(TurnoOperativo $turno, $originTurn)
    {
        if ($this->context->allowsHistoricalTurn() && $this->context->id() === (int)$turno->id) {
            return true;
        }
        return !is_null($originTurn) && (int)$originTurn->id === (int)$turno->id;
    }

    protected function modelAllowsHistoricalAssignment(Model $model)
    {
        return method_exists($model, 'allowsHistoricalTurnoAssignment')
            && $model->allowsHistoricalTurnoAssignment() === true;
    }

    protected function contextDescriptor(Model $model, $contextType, $contextId)
    {
        if (!is_null($contextType) && (int)$contextId > 0) {
            return array('type' => (string)$contextType, 'id' => (int)$contextId);
        }
        // Algunos encabezados heredados (p. ej. Tesorería) no tienen columna
        // pdv_id aunque su formulario sí captura el PDV. El request completa el
        // descriptor sólo durante esa operación; jobs y derivados deben seguir
        // usando atributos persistidos, TurnoContext u origen.
        $requestPdvId = request() ? (int)request()->input('pdv_id') : 0;
        $pdvId = (int)($contextId ?: $model->getAttribute('pdv_id') ?: $requestPdvId);
        return $pdvId > 0 ? array('type' => 'pdv', 'id' => $pdvId) : null;
    }

    protected function companyId(Model $model)
    {
        return $model->getAttribute('core_empresa_id') ?: $model->getAttribute('empresa_id');
    }

    protected function fromPersistedOrigin(Model $model)
    {
        $references = array(
            'inv_doc_encabezado_id' => array(InvDocEncabezado::class),
            'vtas_doc_encabezado_origen_id' => array(FacturaPos::class, VtasDocEncabezado::class),
            'ventas_doc_relacionado_id' => array(FacturaPos::class, VtasDocEncabezado::class),
            'pos_doc_id' => array(FacturaPos::class),
            'sales_doc_id' => array(VtasDocEncabezado::class),
        );

        foreach ($references as $field => $classes) {
            $id = (int)$model->getAttribute($field);
            if ($id <= 0) {
                continue;
            }
            foreach ($classes as $class) {
                $origin = $class::find($id);
                $turno = $this->turnFromModel($origin);
                if (!is_null($turno) && (int)$turno->core_empresa_id === (int)$this->companyId($model)) {
                    $this->lastSource = 'ORIGIN';
                    return $turno;
                }
            }
        }

        return $this->fromDocumentIdentity($model);
    }

    protected function turnFromModel($model)
    {
        if (is_null($model) || empty($model->turno_operativo_id)) {
            return null;
        }
        return TurnoOperativo::find((int)$model->turno_operativo_id);
    }

    protected function fromDocumentIdentity(Model $model)
    {
        $attributes = array('core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo');
        foreach ($attributes as $attribute) {
            if ($model->getAttribute($attribute) === null || $model->getAttribute($attribute) === '') {
                return null;
            }
        }

        $where = array();
        foreach ($attributes as $attribute) {
            $where[$attribute] = $model->getAttribute($attribute);
        }

        foreach (array(FacturaPos::class, VtasDocEncabezado::class) as $class) {
            $instance = new $class();
            if ($model->getTable() === $instance->getTable() || !Schema::hasTable($instance->getTable()) || !Schema::hasColumn($instance->getTable(), 'turno_operativo_id')) {
                continue;
            }
            $source = $class::where($where)->whereNotNull('turno_operativo_id')->orderBy('id', 'DESC')->first();
            $turno = $this->turnFromModel($source);
            if (!is_null($turno)) {
                $this->lastSource = 'DOCUMENT_IDENTITY';
                return $turno;
            }
        }

        return null;
    }

    protected function logFailure(Model $model, $module, $contextId, $contextType, \UnexpectedValueException $exception)
    {
        $empresaId = (int)$this->companyId($model);
        $descriptor = $this->contextDescriptor($model, $contextType, $contextId);
        $receivedId = (int)$model->getAttribute('turno_operativo_id');
        $received = $receivedId > 0 ? TurnoOperativo::find($receivedId) : null;
        $expected = is_null($descriptor)
            ? null
            : $this->manager->currentForContext($empresaId, $descriptor['type'], $descriptor['id']);

        Log::warning('turnos.assignment_failed', array(
            'empresa_id' => $empresaId,
            'modulo' => (string)$module,
            'contexto_tipo' => is_null($descriptor) ? null : $descriptor['type'],
            'contexto_id' => is_null($descriptor) ? null : $descriptor['id'],
            'operacion_tipo' => get_class($model),
            'operacion_id' => $model->exists ? $model->getKey() : null,
            'turno_recibido_id' => $receivedId > 0 ? $receivedId : null,
            'turno_recibido_estado' => is_null($received) ? null : $received->estado,
            'turno_esperado_id' => is_null($expected) ? null : $expected->id,
            'turno_esperado_estado' => is_null($expected) ? null : $expected->estado,
            'fuente_resolucion' => $this->lastSource,
            'error_tipo' => get_class($exception),
            'error' => $exception->getMessage(),
        ));
    }
}
