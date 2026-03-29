<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEstadoToTesoMediosRecaudoTable extends Migration
{
    public function up()
    {
        Schema::table('teso_medios_recaudo', function (Blueprint $table) {
            if (!Schema::hasColumn('teso_medios_recaudo', 'estado')) {
                $table->string('estado')->default('Activo');
            }
        });

        DB::table('teso_medios_recaudo')
            ->whereNull('estado')
            ->orWhere('estado', '')
            ->update(['estado' => 'Activo']);
    }

    public function down()
    {
        Schema::table('teso_medios_recaudo', function (Blueprint $table) {
            if (Schema::hasColumn('teso_medios_recaudo', 'estado')) {
                $table->dropColumn('estado');
            }
        });
    }
}
