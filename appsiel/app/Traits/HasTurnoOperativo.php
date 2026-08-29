<?php

namespace App\Traits;

use App\Core\Services\TurnoAssignmentResolver;

trait HasTurnoOperativo
{
    protected $turnoAssignmentMutationAuthorized = false;

    public static function bootHasTurnoOperativo()
    {
        static::creating(function ($model) {
            if (method_exists($model, 'deferTurnoAssignment') && $model->deferTurnoAssignment()) {
                return;
            }
            app(TurnoAssignmentResolver::class)->assign($model, $model->turnoModuleName());
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
}
