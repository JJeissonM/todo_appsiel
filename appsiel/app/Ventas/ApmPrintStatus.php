<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ApmPrintStatus extends Model
{
    protected $table = 'apm_print_statuses';

    protected $fillable = ['code', 'description'];
}