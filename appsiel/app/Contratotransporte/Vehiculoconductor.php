<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Vehiculoconductor extends Model
{
    protected $table = 'cte_vehiculoconductors';

    protected $fillable = ['id', 'vehiculo_id', 'conductor_id', 'created_at', 'updated_at'];

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
    }
}
