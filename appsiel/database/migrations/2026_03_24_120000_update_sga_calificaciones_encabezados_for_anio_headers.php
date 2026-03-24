<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateSgaCalificacionesEncabezadosForAnioHeaders extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        if (!Schema::hasTable('sga_calificaciones_encabezados')) {
            return;
        }

        Schema::table('sga_calificaciones_encabezados', function (Blueprint $table) {
            if (!Schema::hasColumn('sga_calificaciones_encabezados', 'label')) {
                $table->string('label')->nullable()->after('columna_calificacion');
            }

            if (!Schema::hasColumn('sga_calificaciones_encabezados', 'titulo')) {
                $table->string('titulo')->nullable()->after('label');
            }
        });

        DB::statement('ALTER TABLE sga_calificaciones_encabezados MODIFY periodo_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE sga_calificaciones_encabezados MODIFY curso_id INT UNSIGNED NULL');
        DB::statement('ALTER TABLE sga_calificaciones_encabezados MODIFY asignatura_id INT UNSIGNED NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('sga_calificaciones_encabezados')) {
            return;
        }

        DB::statement('ALTER TABLE sga_calificaciones_encabezados MODIFY periodo_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE sga_calificaciones_encabezados MODIFY curso_id INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE sga_calificaciones_encabezados MODIFY asignatura_id INT UNSIGNED NOT NULL');

        Schema::table('sga_calificaciones_encabezados', function (Blueprint $table) {
            if (Schema::hasColumn('sga_calificaciones_encabezados', 'titulo')) {
                $table->dropColumn('titulo');
            }

            if (Schema::hasColumn('sga_calificaciones_encabezados', 'label')) {
                $table->dropColumn('label');
            }
        });
    }
}
