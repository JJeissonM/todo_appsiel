<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $table = 'cte_conductors';
    protected $fillable = ['id', 'estado', 'tercero_id', 'created_at', 'updated_at'];
}
