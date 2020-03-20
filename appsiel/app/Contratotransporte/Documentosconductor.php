<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Documentosconductor extends Model
{
    protected $table = 'cte_documentosconductors';
    protected $fillable = ['id', 'licencia', 'documento', 'recurso', 'nro_documento', 'vigencia_inicio', 'vigencia_fin', 'conductor_id', 'created_at', 'updated_at'];
}
