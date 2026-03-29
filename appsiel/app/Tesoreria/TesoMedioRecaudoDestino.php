<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoMedioRecaudoDestino extends Model
{
    protected $table = 'teso_medios_recaudo_destinos';

    protected $fillable = [
        'teso_medio_recaudo_id',
        'teso_caja_id',
        'teso_cuenta_bancaria_id',
        'estado'
    ];

    public function medio_recaudo()
    {
        return $this->belongsTo(TesoMedioRecaudo::class, 'teso_medio_recaudo_id');
    }

    public function caja()
    {
        return $this->belongsTo(TesoCaja::class, 'teso_caja_id');
    }

    public function cuenta_bancaria()
    {
        return $this->belongsTo(TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }
}
