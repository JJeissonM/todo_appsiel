<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class RedesSociales extends Model
{
    protected  $table = 'pw_redesSociales';
    protected $fillable = ['icono','nombre','enlace'];
}
