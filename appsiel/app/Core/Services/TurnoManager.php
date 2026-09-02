<?php

namespace App\Core\Services;

use App\Core\TurnoEvento;
use App\Core\TurnoOperativo;
use App\Core\Exceptions\TurnoIntegrityException;
use App\Core\Exceptions\TurnoRequiredException;
use App\Core\Exceptions\TurnoStateException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TurnoManager
{
    protected $modeResolver;
    protected $context;

    public function __construct(TurnoModeResolver $modeResolver, TurnoContext $context)
    {
        $this->modeResolver = $modeResolver;
        $this->context = $context;
    }

    public function enabledForPdv($empresaId, $pdvId, $module = 'ventas_pos')
    {
        return $this->enabledForContext($empresaId, $module, 'pdv', $pdvId);
    }

    public function enabledForContext($empresaId, $module, $contextType, $contextId)
    {
        return $this->modeResolver->enabled($empresaId, $module, $contextType, $contextId);
    }

    public function currentForPdv($empresaId, $pdvId, $lock = false)
    {
        return $this->currentForContext($empresaId, 'pdv', $pdvId, $lock);
    }

    public function currentForContext($empresaId, $contextType, $contextId, $lock = false)
    {
        $query = TurnoOperativo::where('core_empresa_id', (int)$empresaId)
            ->abiertos()
            ->orderBy('id', 'DESC');

        if (!config('turnos.simple_company_mode', false)) {
            $query->where('contexto_tipo', (string)$contextType)
                ->where('contexto_id', (int)$contextId);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->first();
    }

    public function validateNormalOperation(TurnoOperativo $turno, $empresaId, $contextType = null, $contextId = null)
    {
        if ((int)$turno->core_empresa_id !== (int)$empresaId) {
            throw new TurnoIntegrityException('El turno operativo pertenece a otra empresa.');
        }
        if (!config('turnos.simple_company_mode', false)
            && !is_null($contextType)
            && ($turno->contexto_tipo !== (string)$contextType || (int)$turno->contexto_id !== (int)$contextId)) {
            throw new TurnoIntegrityException('El turno operativo no corresponde al contexto donde se intenta operar.');
        }
        if (!$turno->estaAbierto()) {
            throw new TurnoStateException('El turno operativo no está abierto. Abra un turno vigente antes de continuar.');
        }
        return $turno;
    }

    public function requireCurrent($empresaId, $module, $contextType, $contextId)
    {
        $turno = $this->currentForContext($empresaId, $contextType, $contextId);
        if (is_null($turno)) {
            throw new TurnoRequiredException('No existe un turno operativo abierto para este contexto. Debe realizar la apertura antes de continuar.');
        }
        return $this->validateNormalOperation($turno, $empresaId, $contextType, $contextId);
    }

    public function assertCanOpen($empresaId, $pdvId)
    {
        if ($this->enabledForPdv($empresaId, $pdvId) && !is_null($this->currentForPdv($empresaId, $pdvId))) {
            throw new \UnexpectedValueException('El punto de venta ya tiene un turno operativo abierto. Debe cerrarlo antes de iniciar otro.');
        }
    }

    public function openContext($empresaId, $module, $contextType, $contextId, $operationalDate, $userId, $openingBalance = 0, $openedAt = null, array $metadata = array())
    {
        if (!$this->enabledForContext($empresaId, $module, $contextType, $contextId)) {
            throw new TurnoIntegrityException('El contexto solicitado no está configurado en modo TURNOS para el módulo ' . $module . '.');
        }
        $this->requireActor($userId, 'abrir');
        $openedAt = is_null($openedAt) ? date('Y-m-d H:i:s') : $openedAt;
        $manager = $this;

        return DB::transaction(function () use ($empresaId, $module, $contextType, $contextId, $operationalDate, $userId, $openingBalance, $openedAt, $metadata, $manager) {
            $manager->lockCompany($empresaId);
            $manager->modeResolver->clearCache();
            if (!$manager->enabledForContext($empresaId, $module, $contextType, $contextId)) {
                throw new TurnoIntegrityException('La configuración cambió y el contexto ya no opera en modo TURNOS. No se realizó la apertura.');
            }
            if (!is_null($manager->currentForContext($empresaId, $contextType, $contextId, true))) {
                throw new TurnoStateException('Ya existe un turno abierto para este contexto operativo.');
            }
            $turno = TurnoOperativo::create(array(
                'core_empresa_id' => $empresaId,
                'contexto_tipo' => $contextType,
                'contexto_id' => $contextId,
                'pdv_id' => $contextType === 'pdv' ? $contextId : null,
                'teso_caja_id' => $contextType === 'caja' ? $contextId : null,
                'fecha_operativa' => $operationalDate,
                'abierto_en' => $openedAt,
                'abierto_por' => $userId,
                'saldo_inicial' => $openingBalance,
                'estado' => TurnoOperativo::ESTADO_ABIERTO,
                'codigo' => $manager->buildTurnCode(
                    $empresaId,
                    $contextType,
                    $contextId,
                    $openedAt,
                    str_replace('.', '', uniqid('', true))
                ),
                'clave_contexto_abierto' => $manager->contextKey($empresaId, $contextType, $contextId),
            ));
            $manager->recordEvent($turno, 'APERTURA', null, TurnoOperativo::ESTADO_ABIERTO, null, $userId, null, $metadata);
            $manager->context->set($turno);
            return $turno;
        });
    }

    public function close(TurnoOperativo $target, $userId, $closingBalance = null, $reason = null, $closedAt = null)
    {
        $this->requireActor($userId, 'cerrar');
        $manager = $this;
        $updated = DB::transaction(function () use ($target, $userId, $closingBalance, $reason, $closedAt, $manager) {
            $turno = TurnoOperativo::where('id', $target->id)->lockForUpdate()->firstOrFail();
            if (!$turno->estaAbierto()) {
                throw new TurnoStateException('Sólo un turno abierto puede cerrarse.');
            }
            $turno->cerrado_en = is_null($closedAt) ? date('Y-m-d H:i:s') : $closedAt;
            $turno->cerrado_por = $userId;
            $turno->saldo_cierre = $closingBalance;
            $turno->estado = TurnoOperativo::ESTADO_CERRADO;
            $turno->clave_contexto_abierto = null;
            $turno->authorizeStateTransition();
            $turno->save();
            $manager->recordEvent($turno, 'CIERRE', TurnoOperativo::ESTADO_ABIERTO, TurnoOperativo::ESTADO_CERRADO, null, $userId, $reason);
            return $turno;
        });
        $target->setRawAttributes($updated->getAttributes(), true);
        $this->context->clear();
        return $target;
    }

    public function assertCanClose($empresaId, $pdvId)
    {
        if ($this->enabledForPdv($empresaId, $pdvId) && is_null($this->currentForPdv($empresaId, $pdvId))) {
            throw new \UnexpectedValueException('El punto de venta no tiene un turno operativo abierto para cerrar.');
        }
    }

    public function openFromLegacy(Model $opening, $userId = null)
    {
        $manager = $this;
        return DB::transaction(function () use ($opening, $userId, $manager) {
            $manager->lockCompany($opening->core_empresa_id);
            $manager->modeResolver->clearCache();
            if (!$manager->enabledForPdv($opening->core_empresa_id, $opening->pdv_id)) {
                return null;
            }
            $existing = $manager->currentForPdv($opening->core_empresa_id, $opening->pdv_id, true);
            if (!is_null($existing)) {
                throw new \UnexpectedValueException('Ya existe un turno operativo abierto para este punto de venta.');
            }

            $openedAt = $opening->created_at ?: date('Y-m-d H:i:s');
            $actorId = $userId ?: $opening->cajero_id;
            $manager->requireActor($actorId, 'abrir');
            $contextKey = $manager->contextKey($opening->core_empresa_id, 'pdv', $opening->pdv_id);
            $pdv = \App\VentasPos\Pdv::find($opening->pdv_id);
            $turno = TurnoOperativo::create(array(
                'core_empresa_id' => $opening->core_empresa_id,
                'contexto_tipo' => 'pdv',
                'contexto_id' => $opening->pdv_id,
                'pdv_id' => $opening->pdv_id,
                'teso_caja_id' => is_null($pdv) ? null : $pdv->caja_default_id,
                'fecha_operativa' => substr((string)$opening->fecha, 0, 10),
                'abierto_en' => $openedAt,
                'abierto_por' => $actorId,
                'saldo_inicial' => $opening->efectivo_base ?: 0,
                'estado' => TurnoOperativo::ESTADO_ABIERTO,
                'codigo' => $manager->buildTurnCode(
                    $opening->core_empresa_id,
                    'pdv',
                    $opening->pdv_id,
                    $openedAt,
                    $opening->id,
                    is_null($pdv) ? null : $pdv->descripcion
                ),
                'clave_contexto_abierto' => $contextKey,
            ));

            $opening->turno_operativo_id = $turno->id;
            $opening->save();
            $manager->recordEvent($turno, 'APERTURA', null, TurnoOperativo::ESTADO_ABIERTO, $opening, $actorId, null);
            $manager->context->set($turno);

            return $turno;
        });
    }

    public function closeFromLegacy(Model $closing, $userId = null, $closingBalance = null)
    {
        $manager = $this;
        return DB::transaction(function () use ($closing, $userId, $closingBalance, $manager) {
            $manager->lockCompany($closing->core_empresa_id);
            $manager->modeResolver->clearCache();
            if (!$manager->enabledForPdv($closing->core_empresa_id, $closing->pdv_id)) {
                return null;
            }
            $turno = $manager->currentForPdv($closing->core_empresa_id, $closing->pdv_id, true);
            if (is_null($turno)) {
                throw new \UnexpectedValueException('No existe un turno operativo abierto para este punto de venta.');
            }

            $previous = $turno->estado;
            $actorId = $userId ?: $closing->cajero_id;
            $manager->requireActor($actorId, 'cerrar');
            $turno->cerrado_en = $closing->created_at ?: date('Y-m-d H:i:s');
            $turno->cerrado_por = $actorId;
            $turno->saldo_cierre = $closingBalance;
            $turno->estado = TurnoOperativo::ESTADO_CERRADO;
            $turno->clave_contexto_abierto = null;
            $turno->authorizeStateTransition();
            $turno->save();

            $closing->turno_operativo_id = $turno->id;
            // La fecha del cierre historico se conserva; la fecha operativa vive en el turno.
            $closing->save();
            $manager->recordEvent($turno, 'CIERRE', $previous, $turno->estado, $closing, $actorId, $closing->getAttribute('detalle'));
            $manager->context->clear();

            return $turno;
        });
    }

    public function assignAdjustment(Model $movement, TurnoOperativo $turno, $reason, $userId = null, $idempotencyKey = null)
    {
        if (trim((string)$reason) === '') {
            throw new \InvalidArgumentException('Los ajustes posteriores deben indicar un motivo de auditoria.');
        }
        $this->requireActor($userId, 'registrar un ajuste');
        $movementCompany = $movement->getAttribute('core_empresa_id') ?: $movement->getAttribute('empresa_id');
        if ((int)$movementCompany !== (int)$turno->core_empresa_id) {
            throw new \UnexpectedValueException('El movimiento y el turno deben pertenecer a la misma empresa.');
        }
        if (!$movement->exists && trim((string)$idempotencyKey) === '') {
            throw new TurnoIntegrityException('Un ajuste sobre una entidad nueva requiere una clave de idempotencia explícita.');
        }

        $operationKey = trim((string)$idempotencyKey);
        if ($operationKey === '') {
            $operationKey = sha1(get_class($movement) . '|' . $movement->getKey() . '|' . $turno->id . '|' . $userId . '|' . trim((string)$reason));
        }

        $manager = $this;
        return DB::transaction(function () use ($movement, $turno, $reason, $userId, $operationKey, $manager) {
            $lockedTurn = TurnoOperativo::where('id', $turno->id)->lockForUpdate()->firstOrFail();
            if (!in_array($lockedTurn->estado, array(TurnoOperativo::ESTADO_CERRADO, TurnoOperativo::ESTADO_AUDITADO), true)) {
                throw new TurnoStateException('Los ajustes posteriores sólo pueden asociarse a turnos cerrados o auditados.');
            }

            $eventData = array('idempotency_key' => $operationKey);
            $existing = TurnoEvento::where('turno_operativo_id', $lockedTurn->id)
                ->where('tipo', 'AJUSTE_POSTERIOR')
                ->where('datos', json_encode($eventData))
                ->first();
            if (!is_null($existing)) {
                return $movement;
            }

            $createdAt = $movement->getAttribute('created_at');
            $movement->setAttribute('turno_operativo_id', $lockedTurn->id);
            if (method_exists($movement, 'authorizeTurnoAssignmentMutation')) {
                $movement->authorizeTurnoAssignmentMutation();
            }
            $manager->context->runFromOrigin($lockedTurn, 'turno_ajuste', $lockedTurn->id, function () use ($movement) {
                $movement->save();
            });
            if ($movement->exists && !is_null($createdAt) && (string)$createdAt !== (string)$movement->getAttribute('created_at')) {
                throw new TurnoIntegrityException('Un ajuste no puede modificar la fecha real de creación del movimiento.');
            }
            $manager->recordEvent($lockedTurn, 'AJUSTE_POSTERIOR', $lockedTurn->estado, $lockedTurn->estado, $movement, $userId, $reason, $eventData);
            return $movement;
        });
    }

    public function reopen(TurnoOperativo $turno, $reason, $userId = null)
    {
        if (trim((string)$reason) === '') {
            throw new \InvalidArgumentException('La reapertura requiere un motivo de auditoria.');
        }
        $this->requireActor($userId, 'reabrir');

        $manager = $this;
        return DB::transaction(function () use ($turno, $reason, $userId, $manager) {
            $turno = TurnoOperativo::where('id', $turno->id)->lockForUpdate()->firstOrFail();
            if (!in_array($turno->estado, array(TurnoOperativo::ESTADO_CERRADO, TurnoOperativo::ESTADO_AUDITADO), true)) {
                throw new TurnoStateException('Sólo un turno cerrado o auditado puede reabrirse mediante autorización excepcional.');
            }
            if (!is_null($manager->currentForContext($turno->core_empresa_id, $turno->contexto_tipo, $turno->contexto_id, true))) {
                throw new \UnexpectedValueException('Ya existe otro turno abierto para este contexto.');
            }
            $previous = $turno->estado;
            $previousClosure = array(
                'cerrado_en' => (string)$turno->cerrado_en,
                'cerrado_por' => $turno->cerrado_por,
                'saldo_cierre' => $turno->saldo_cierre,
            );
            $turno->estado = TurnoOperativo::ESTADO_ABIERTO;
            $turno->cerrado_en = null;
            $turno->cerrado_por = null;
            $turno->saldo_cierre = null;
            $turno->clave_contexto_abierto = $manager->contextKey($turno->core_empresa_id, $turno->contexto_tipo, $turno->contexto_id);
            $turno->authorizeStateTransition();
            $turno->save();
            $manager->recordEvent($turno, 'REAPERTURA', $previous, $turno->estado, null, $userId, $reason, $previousClosure);
            return $turno;
        });
    }

    public function startAudit(TurnoOperativo $turno, $userId = null, $reason = null)
    {
        $this->requireActor($userId, 'iniciar la auditoría');
        if ($turno->estado !== TurnoOperativo::ESTADO_CERRADO) {
            throw new \UnexpectedValueException('Solo un turno cerrado puede pasar a auditoria.');
        }
        return $this->transition($turno, TurnoOperativo::ESTADO_CERRADO, TurnoOperativo::ESTADO_AUDITANDO, 'INICIO_AUDITORIA', $userId, $reason);
    }

    public function completeAudit(TurnoOperativo $turno, $userId = null, $reason = null)
    {
        $this->requireActor($userId, 'finalizar la auditoría');
        if ($turno->estado !== TurnoOperativo::ESTADO_AUDITANDO) {
            throw new \UnexpectedValueException('El turno debe estar en auditoria antes de marcarse auditado.');
        }
        return $this->transition($turno, TurnoOperativo::ESTADO_AUDITANDO, TurnoOperativo::ESTADO_AUDITADO, 'FIN_AUDITORIA', $userId, $reason);
    }

    protected function transition(TurnoOperativo $target, $expectedState, $newState, $event, $userId, $reason)
    {
        $manager = $this;
        $updated = DB::transaction(function () use ($target, $expectedState, $newState, $event, $userId, $reason, $manager) {
            $turno = TurnoOperativo::where('id', $target->id)->lockForUpdate()->firstOrFail();
            if ($turno->estado !== $expectedState) {
                throw new TurnoStateException('El estado del turno cambió durante la operación; recargue la información e intente nuevamente.');
            }
            $previous = $turno->estado;
            $turno->estado = $newState;
            $turno->authorizeStateTransition();
            $turno->save();
            $manager->recordEvent($turno, $event, $previous, $newState, null, $userId, $reason);
            return $turno;
        });
        $target->setRawAttributes($updated->getAttributes(), true);
        return $target;
    }

    protected function requireActor($userId, $action)
    {
        if ((int)$userId <= 0) {
            throw new TurnoIntegrityException('Debe identificar el usuario responsable para ' . $action . ' el turno.');
        }
    }

    protected function contextKey($empresaId, $contextType, $contextId)
    {
        if (config('turnos.simple_company_mode', false)) {
            return 'empresa:' . (int)$empresaId;
        }
        return (string)$contextType . ':' . (int)$empresaId . ':' . (int)$contextId;
    }

    protected function buildTurnCode($empresaId, $contextType, $contextId, $openedAt, $uniqueSuffix, $contextLabel = null)
    {
        if (is_null($contextLabel) && (string)$contextType === 'pdv') {
            $contextLabel = DB::table('vtas_pos_puntos_de_ventas')
                ->where('id', (int)$contextId)
                ->where('core_empresa_id', (int)$empresaId)
                ->value('descripcion');
        }
        if (trim((string)$contextLabel) === '') {
            $contextLabel = (string)$contextType;
        }

        $label = strtoupper(trim((string)str_slug($contextLabel, '-'), '-'));
        if ($label === '') {
            $label = strtoupper((string)$contextType);
        }

        $suffix = preg_replace('/[^A-Za-z0-9]/', '', (string)$uniqueSuffix);
        if ($suffix === '') {
            $suffix = str_replace('.', '', uniqid('', true));
        }
        $timestamp = date('YmdHis', strtotime($openedAt));
        $tail = '-' . (int)$empresaId . '-' . (int)$contextId . '-' . $timestamp . '-' . $suffix;

        // codigo tiene una longitud máxima de 80. Se recorta solamente el nombre
        // descriptivo; empresa, contexto, fecha y sufijo de unicidad se conservan.
        $maxLabelLength = 80 - strlen('TUR-') - strlen($tail);
        $label = rtrim(substr($label, 0, max(1, $maxLabelLength)), '-');

        return 'TUR-' . $label . $tail;
    }

    protected function lockCompany($empresaId)
    {
        DB::table('core_empresas')->where('id', (int)$empresaId)->lockForUpdate()->first();
    }

    public function recordEvent(TurnoOperativo $turno, $type, $previous, $new, Model $entity = null, $userId = null, $reason = null, array $data = array())
    {
        return TurnoEvento::create(array(
            'turno_operativo_id' => $turno->id,
            'tipo' => $type,
            'estado_anterior' => $previous,
            'estado_nuevo' => $new,
            'entidad_tipo' => is_null($entity) ? null : get_class($entity),
            'entidad_id' => is_null($entity) ? null : $entity->getKey(),
            'usuario_id' => $userId,
            'motivo' => $reason,
            'datos' => empty($data) ? null : json_encode($data),
        ));
    }
}
