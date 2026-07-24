<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddPdvAndTurnRangeToTesoArqueosCaja extends Migration
{
    public function up()
    {
        $hasPdvId = Schema::hasColumn('teso_arqueos_caja', 'pdv_id');
        $hasFechaHoraApertura = Schema::hasColumn('teso_arqueos_caja', 'fecha_hora_apertura');
        $hasFechaHoraCierre = Schema::hasColumn('teso_arqueos_caja', 'fecha_hora_cierre');

        Schema::table('teso_arqueos_caja', function (Blueprint $table) use ($hasPdvId, $hasFechaHoraApertura, $hasFechaHoraCierre) {
            if (!$hasPdvId) {
                $table->integer('pdv_id')->unsigned()->nullable()->after('teso_caja_id');
                $table->index('pdv_id', 'teso_arqueos_caja_pdv_id_idx');
            }

            if (!$hasFechaHoraApertura) {
                $table->dateTime('fecha_hora_apertura')->nullable()->after('pdv_id');
                $table->index('fecha_hora_apertura', 'teso_arqueos_caja_apertura_idx');
            }

            if (!$hasFechaHoraCierre) {
                $table->dateTime('fecha_hora_cierre')->nullable()->after('fecha_hora_apertura');
                $table->index('fecha_hora_cierre', 'teso_arqueos_caja_cierre_idx');
            }
        });
    }

    public function down()
    {
        $hasPdvId = Schema::hasColumn('teso_arqueos_caja', 'pdv_id');
        $hasFechaHoraApertura = Schema::hasColumn('teso_arqueos_caja', 'fecha_hora_apertura');
        $hasFechaHoraCierre = Schema::hasColumn('teso_arqueos_caja', 'fecha_hora_cierre');

        Schema::table('teso_arqueos_caja', function (Blueprint $table) use ($hasPdvId, $hasFechaHoraApertura, $hasFechaHoraCierre) {
            if ($hasFechaHoraCierre) {
                $table->dropIndex('teso_arqueos_caja_cierre_idx');
                $table->dropColumn('fecha_hora_cierre');
            }

            if ($hasFechaHoraApertura) {
                $table->dropIndex('teso_arqueos_caja_apertura_idx');
                $table->dropColumn('fecha_hora_apertura');
            }

            if ($hasPdvId) {
                $table->dropIndex('teso_arqueos_caja_pdv_id_idx');
                $table->dropColumn('pdv_id');
            }
        });
    }
}
