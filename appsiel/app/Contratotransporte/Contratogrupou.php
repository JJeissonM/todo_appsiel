<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Contratogrupou extends Model
{
    protected $table = 'cte_contratogrupous';
    protected $fillable = ['id', 'identificacion', 'persona', 'contrato_id', 'created_at'];
}
