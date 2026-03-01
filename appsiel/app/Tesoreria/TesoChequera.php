<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoChequera extends Model
{
    protected $table = 'teso_chequeras';

    protected $fillable = [
        'teso_cuenta_bancaria_id',
        'descripcion',
        'numero_inicial',
        'numero_final',
        'consecutivo_actual',
        'estado'
    ];

    public function cuenta_bancaria()
    {
        return $this->belongsTo(TesoCuentaBancaria::class, 'teso_cuenta_bancaria_id');
    }
}
