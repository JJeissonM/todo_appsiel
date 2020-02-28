<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Formcontactenos extends Model
{
    protected $table = 'pw_formcontactenos';
    protected $fillable = ['id', 'names', 'email', 'subject', 'message', 'state', 'created_at', 'updated_at'];
}
