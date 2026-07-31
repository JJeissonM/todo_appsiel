<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddHoursToInvMovimientos extends Migration
{
    public function up()
    {
        $hasHoraInicio = Schema::hasColumn('inv_movimientos', 'hora_inicio');
        $hasHoraFinalizacion = Schema::hasColumn('inv_movimientos', 'hora_finalizacion');

        Schema::table('inv_movimientos', function (Blueprint $table) use ($hasHoraInicio, $hasHoraFinalizacion) {
            if (!$hasHoraInicio) {
                // Nullable conserva intactos los movimientos historicos y las integraciones que trabajan solo con fecha.
                $table->time('hora_inicio')->nullable()->after('fecha');
            }

            if (!$hasHoraFinalizacion) {
                $table->time('hora_finalizacion')->nullable()->after('hora_inicio');
            }
        });
    }

    public function down()
    {
        $hasHoraInicio = Schema::hasColumn('inv_movimientos', 'hora_inicio');
        $hasHoraFinalizacion = Schema::hasColumn('inv_movimientos', 'hora_finalizacion');

        Schema::table('inv_movimientos', function (Blueprint $table) use ($hasHoraInicio, $hasHoraFinalizacion) {
            if ($hasHoraFinalizacion) {
                $table->dropColumn('hora_finalizacion');
            }

            if ($hasHoraInicio) {
                $table->dropColumn('hora_inicio');
            }
        });
    }
}
