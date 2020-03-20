<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Planillac extends Model
{
    protected $table = 'cte_planillacs';
    protected $fillable = ['id', 'razon_social', 'nit', 'convenio', 'contrato_id', 'plantilla_id', 'created_at', 'updated_at'];
}
