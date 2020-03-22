<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Mantreportes extends Model
{
    protected $table = 'cte_mantreportes';
    protected $fillable = ['id', 'fecha_suceso', 'reporte', 'mantenimiento_id', 'created_at', 'updated_at'];
}
