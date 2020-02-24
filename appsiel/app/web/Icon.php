<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Icon extends Model
{
    protected  $table = 'pw_icons';
    protected  $fillable = ['id', 'icono', 'created_at', 'updated_at'];

}
