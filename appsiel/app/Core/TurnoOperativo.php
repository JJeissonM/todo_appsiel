<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class TurnoOperativo extends Model
{
    const ESTADO_ABIERTO = 'ABIERTO';
    const ESTADO_CERRADO = 'CERRADO';
    const ESTADO_AUDITANDO = 'AUDITANDO';
    const ESTADO_AUDITADO = 'AUDITADO';

    protected $table = 'core_turnos_operativos';

    protected $fillable = array(
        'core_empresa_id', 'contexto_tipo', 'contexto_id', 'pdv_id', 'teso_caja_id',
        'fecha_operativa', 'abierto_en', 'cerrado_en', 'abierto_por', 'cerrado_por',
        'saldo_inicial', 'saldo_cierre', 'estado', 'codigo', 'clave_contexto_abierto', 'observaciones'
    );

    protected $dates = array('abierto_en', 'cerrado_en');

    protected $stateTransitionAuthorized = false;

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($turno) {
            $identityFields = array('core_empresa_id', 'contexto_tipo', 'contexto_id', 'pdv_id', 'teso_caja_id', 'fecha_operativa', 'abierto_en', 'codigo');
            foreach ($identityFields as $field) {
                if ($turno->isDirty($field)) {
                    throw new \App\Core\Exceptions\TurnoIntegrityException('La identidad del turno es inmutable después de la apertura. No se puede modificar ' . $field . '.');
                }
            }
            $previousState = $turno->getOriginal('estado');
            if ($turno->isDirty('estado') && !$turno->stateTransitionAuthorized) {
                throw new \App\Core\Exceptions\TurnoStateException('Los cambios de estado del turno deben realizarse mediante TurnoManager.');
            }
            if ($previousState === self::ESTADO_AUDITADO && !$turno->stateTransitionAuthorized && $turno->isDirty()) {
                throw new \App\Core\Exceptions\TurnoStateException('Un turno auditado es inmutable; utilice un ajuste o proceso excepcional autorizado.');
            }
            $turno->stateTransitionAuthorized = false;
        });
    }

    public function authorizeStateTransition()
    {
        $this->stateTransitionAuthorized = true;
        return $this;
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'core_empresa_id');
    }

    public function pdv()
    {
        return $this->belongsTo('App\VentasPos\Pdv', 'pdv_id');
    }

    public function eventos()
    {
        return $this->hasMany(TurnoEvento::class, 'turno_operativo_id');
    }

    public function scopeAbiertos($query)
    {
        return $query->where('estado', self::ESTADO_ABIERTO);
    }

    public function estaAbierto()
    {
        return $this->estado === self::ESTADO_ABIERTO && is_null($this->cerrado_en);
    }
}
