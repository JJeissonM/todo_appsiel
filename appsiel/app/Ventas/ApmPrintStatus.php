<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ApmPrintStatus extends Model
{
    protected $table = 'apm_print_statuses';

    // Estados permitidos del APM: pending (pendiente), printed (impreso),
    // failed (error de impresion), cancelled (cancelado) y retired (retirado manualmente).
    protected $fillable = ['code', 'description'];
}
