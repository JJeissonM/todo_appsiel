<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Documentosconductor extends Model
{
    protected $table = 'cte_documentosconductors';
    protected $fillable = ['id', 'conductor_id', 'licencia', 'documento', 'recurso', 'nro_documento', 'vigencia_inicio', 'vigencia_fin', 'created_at', 'updated_at'];
}
