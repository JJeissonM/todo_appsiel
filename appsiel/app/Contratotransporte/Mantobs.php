<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Mantobs extends Model
{
    protected $table = 'cte_mantobs';
    protected $fillable = ['id', 'fecha_suceso', 'observacion', 'mantenimiento_id', 'created_at', 'updated_at'];
}
