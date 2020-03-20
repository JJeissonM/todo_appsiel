<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantillaarticulo extends Model
{
    protected $table = 'cte_plantillaarticulos';
    protected $fillable = ['id', 'titulo', 'texto', 'plantilla_id', 'created_at', 'updated_at'];
}
