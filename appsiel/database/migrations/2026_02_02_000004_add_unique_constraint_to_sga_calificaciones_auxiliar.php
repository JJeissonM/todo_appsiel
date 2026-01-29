<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddUniqueConstraintToSgaCalificacionesAuxiliar extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sga_calificaciones_auxiliares')) {
            return;
        }

        DB::statement(
            'DELETE t1 FROM sga_calificaciones_auxiliares t1
             INNER JOIN sga_calificaciones_auxiliares t2
             ON t1.id_colegio = t2.id_colegio
             AND t1.anio = t2.anio
             AND t1.id_periodo = t2.id_periodo
             AND t1.curso_id = t2.curso_id
             AND t1.id_asignatura = t2.id_asignatura
             AND t1.id_estudiante = t2.id_estudiante
             AND t1.codigo_matricula = t2.codigo_matricula
             AND t1.id > t2.id'
        );

        Schema::table('sga_calificaciones_auxiliares', function (Blueprint $table) {
            $table->unique(
                ['id_colegio', 'anio', 'id_periodo', 'curso_id', 'id_asignatura', 'id_estudiante', 'codigo_matricula'],
                'sga_calificaciones_auxiliares_unq'
            );
        });
    }

    public function down()
    {
        if (!Schema::hasTable('sga_calificaciones_auxiliares')) {
            return;
        }

        Schema::table('sga_calificaciones_auxiliares', function (Blueprint $table) {
            $table->dropUnique('sga_calificaciones_auxiliares_unq');
        });
    }
}
