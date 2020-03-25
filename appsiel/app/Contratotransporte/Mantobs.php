<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Mantobs extends Model
{
    protected $table = 'cte_mantobs';
    protected $fillable = ['id', 'mantenimiento_id', 'fecha_suceso', 'observacion', 'created_at', 'updated_at'];
}
