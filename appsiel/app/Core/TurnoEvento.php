<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class TurnoEvento extends Model
{
    protected $table = 'core_turno_eventos';

    protected $fillable = array(
        'turno_operativo_id', 'tipo', 'estado_anterior', 'estado_nuevo',
        'entidad_tipo', 'entidad_id', 'usuario_id', 'motivo', 'datos'
    );

    public function turno()
    {
        return $this->belongsTo(TurnoOperativo::class, 'turno_operativo_id');
    }
}
