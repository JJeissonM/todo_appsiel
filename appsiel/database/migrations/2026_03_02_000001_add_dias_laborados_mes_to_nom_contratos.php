<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiasLaboradosMesToNomContratos extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nom_contratos')) {
            return;
        }

        Schema::table('nom_contratos', function (Blueprint $table) {
            if (!Schema::hasColumn('nom_contratos', 'dias_laborados_mes')) {
                $table->unsignedTinyInteger('dias_laborados_mes')
                    ->nullable()
                    ->after('tipo_cotizante');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('nom_contratos')) {
            return;
        }

        Schema::table('nom_contratos', function (Blueprint $table) {
            if (Schema::hasColumn('nom_contratos', 'dias_laborados_mes')) {
                $table->dropColumn('dias_laborados_mes');
            }
        });
    }
}
