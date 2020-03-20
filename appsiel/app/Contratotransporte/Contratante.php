<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Contratante extends Model
{
    protected $table = 'cte_contratantes';
    protected $fillable = ['id', 'estado', 'tercero_id', 'created_at', 'updated_at'];
}
