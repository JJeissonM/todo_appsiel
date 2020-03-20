<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Numeraltabla extends Model
{
    protected $table = 'cte_numeraltablas';
    protected $fillable = ['id', 'campo', 'valor', 'plantillaarticulonumeral_id', 'created_at', 'updated_at'];
}
