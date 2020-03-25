<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Mantreportes extends Model
{
    protected $table = 'cte_mantreportes';
    protected $fillable = ['id', 'mantenimiento_id', 'fecha_suceso', 'reporte', 'created_at', 'updated_at'];
}
