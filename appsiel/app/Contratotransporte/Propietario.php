<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Propietario extends Model
{
    protected $table = 'cte_propietarios';
    protected $fillable = ['id', 'genera_planilla', 'tercero_id', 'created_at', 'updated_at'];
}
