<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixPazYSalvoDateFilter extends Migration
{
    public function up()
    {
        DB::table('sys_campos')
            ->where('definicion', 'gestion_documental/paz_y_salvo')
            ->where('name', 'fecha_expedicion')
            ->where('tipo', 'fecha')
            ->update(['tipo' => 'date', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    public function down()
    {
        // No restaurar el tipo fecha: no es válido para el formulario de reportes.
    }
}
