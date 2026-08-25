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
