<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniqueConstraintToCalificacionesEncabezados extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('sga_calificaciones_encabezados')) {
            return;
        }

        // Remove duplicates before adding a unique constraint.
        DB::statement(
            'DELETE t1 FROM sga_calificaciones_encabezados t1
             INNER JOIN sga_calificaciones_encabezados t2
             ON t1.columna_calificacion = t2.columna_calificacion
             AND t1.periodo_id = t2.periodo_id
             AND t1.curso_id = t2.curso_id
             AND t1.asignatura_id = t2.asignatura_id
             AND t1.id > t2.id'
        );

        Schema::table('sga_calificaciones_encabezados', function (Blueprint $table) {
            $table->unique(
                ['columna_calificacion', 'periodo_id', 'curso_id', 'asignatura_id'],
                'sga_calificaciones_encabezados_unq'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('sga_calificaciones_encabezados')) {
            return;
        }

        Schema::table('sga_calificaciones_encabezados', function (Blueprint $table) {
            $table->dropUnique('sga_calificaciones_encabezados_unq');
        });
    }
}
