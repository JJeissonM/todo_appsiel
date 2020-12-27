<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Planillaconductor extends Model
{
    protected $table = 'cte_planillaconductors';
    protected $fillable = ['id', 'conductor_id', 'planillac_id', 'created_at', 'updated_at'];


    public function conductor()
    {
        return $this->belongsTo(Conductor::class);
    }

    public function planillac()
    {
        return $this->belongsTo(Planillac::class);
    }
}
