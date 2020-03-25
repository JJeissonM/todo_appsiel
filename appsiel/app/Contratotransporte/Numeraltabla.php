<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Numeraltabla extends Model
{
    protected $table = 'cte_numeraltablas';
    protected $fillable = ['id', 'plantillaarticulonumeral_id', 'campo', 'valor', 'created_at', 'updated_at'];
}
