<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $table = 'cte_mantenimientos';
    protected $fillable = ['id', 'fecha', 'sede', 'revisado', 'anioperiodo_id', 'created_at', 'updated_at'];
}
