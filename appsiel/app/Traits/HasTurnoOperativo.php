<?php

namespace App\Traits;

use App\Core\Services\TurnoAssignmentResolver;
use App\Core\TurnoOperativo;
use Illuminate\Support\Facades\Auth;

trait HasTurnoOperativo
{
    protected $turnoAssignmentMutationAuthorized = false;
    protected $turnoCreationAdjustment = null;

    public static function bootHasTurnoOperativo()
    {
        static::creating(function ($model) {
            $model->prepareTurnoCreationAdjustment();
            if (method_exists($model, 'deferTurnoAssignment') && $model->deferTurnoAssignment()) {
                return;
            }
            app(TurnoAssignmentResolver::class)->assign($model, $model->turnoModuleName());
        });
        static::created(function ($model) {
            $model->recordTurnoCreationAdjustment();
        });
        static::updating(function ($model) {
            $originalTurnId = (int)$model->getOriginal('turno_operativo_id');
            $currentTurnId = (int)$model->getAttribute('turno_operativo_id');
            $changedTurn = $model->isDirty('turno_operativo_id') && $originalTurnId !== $currentTurnId;
            if ($changedTurn && !$model->turnoAssignmentMutationAuthorized) {
                throw new \App\Core\Exceptions\TurnoIntegrityException(
                    'El turno operativo de una transacción es inmutable después de crearla. Registre una nueva operación o un ajuste autorizado.'
                );
            }
            $model->turnoAssignmentMutationAuthorized = false;
        });
    }

    public function turnoOperativo()
    {
        return $this->belongsTo('App\Core\TurnoOperativo', 'turno_operativo_id');
    }

    public function scopeDelTurno($query, $turnoId)
    {
        return $query->where($this->getTable() . '.turno_operativo_id', (int)$turnoId);
    }

    protected function turnoModuleName()
    {
        return 'core';
    }

    public function getTurnoModuleName()
    {
        return $this->turnoModuleName();
    }

    public function authorizeTurnoAssignmentMutation()
    {
        $this->turnoAssignmentMutationAuthorized = true;
        return $this;
    }

    public function allowsHistoricalTurnoAssignment()
    {
        return !is_null($this->turnoCreationAdjustment);
    }

    protected function prepareTurnoCreationAdjustment()
    {
        $turnId = (int)$this->getAttribute('turno_operativo_id');
        if ($turnId <= 0) {
            return;
        }
        $turno = TurnoOperativo::find($turnId);
        if (is_null($turno) || $turno->estaAbierto()) {
            return;
        }
        if (!in_array($turno->estado, array(TurnoOperativo::ESTADO_CERRADO, TurnoOperativo::ESTADO_AUDITADO), true)) {
            return;
        }

        $requestData = request()->all();
        if (!array_key_exists('turno_ajuste_motivo', $requestData)) {
            // Sin intención explícita de ajuste, el resolver conserva el rechazo
            // normal o la propagación histórica legítima desde un documento origen.
            return;
        }

        $user = Auth::user();
        if (is_null($user) || !$user->can('turnos.ajustes.registrar')) {
            throw new \App\Core\Exceptions\TurnoIntegrityException(
                'No tiene permiso para registrar operaciones de ajuste sobre un turno cerrado o auditado.'
            );
        }
        $reason = trim((string)$requestData['turno_ajuste_motivo']);
        if ($reason === '') {
            throw new \App\Core\Exceptions\TurnoIntegrityException(
                'Debe indicar el motivo del ajuste para utilizar un turno cerrado o auditado.'
            );
        }

        $this->turnoCreationAdjustment = array(
            'turno' => $turno,
            'motivo' => $reason,
            'usuario_id' => (int)$user->id,
        );
    }

    protected function recordTurnoCreationAdjustment()
    {
        if (is_null($this->turnoCreationAdjustment)) {
            return;
        }
        $adjustment = $this->turnoCreationAdjustment;
        app(\App\Core\Services\TurnoManager::class)->assignAdjustment(
            $this,
            $adjustment['turno'],
            $adjustment['motivo'],
            $adjustment['usuario_id']
        );
        $this->turnoCreationAdjustment = null;
    }
}
