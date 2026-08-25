<?php

namespace App\Traits;

use App\Core\Services\TurnoAssignmentResolver;

trait HasTurnoOperativo
{
    public static function bootHasTurnoOperativo()
    {
        static::creating(function ($model) {
            app(TurnoAssignmentResolver::class)->assign($model, $model->turnoModuleName());
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
}
