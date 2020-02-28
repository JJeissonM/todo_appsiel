<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Footer extends Model
{
    protected  $table = 'pw_footer';
    protected $fillable = ['ubicacion', 'copyright', 'texto', 'background', 'color', 'created_at', 'updated_at'];
}
