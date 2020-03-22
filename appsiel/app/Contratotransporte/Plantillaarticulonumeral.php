<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantillaarticulonumeral extends Model
{
    protected $table = 'cte_plantillaarticulonumerals';
    protected $fillable = ['id', 'numeracion', 'texto', 'plantillaarticulo_id', 'created_at', 'updated_at'];
}
