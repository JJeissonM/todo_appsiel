<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Anioperiodo extends Model
{
    protected $table = 'cte_anioperiodos';
    protected $fillable = ['id', 'inicio', 'fin', 'anio_id', 'created_at', 'updated_at'];
}
