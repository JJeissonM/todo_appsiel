<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCantidadGuiasToSgaCursoTieneAsignaturasTable extends Migration
{
    public function up()
    {
        if ( !Schema::hasTable('sga_curso_tiene_asignaturas') ) {
            return;
        }

        Schema::table('sga_curso_tiene_asignaturas', function (Blueprint $table) {
            if ( !Schema::hasColumn('sga_curso_tiene_asignaturas', 'cantidad_guias') ) {
                $table->unsignedInteger('cantidad_guias')->nullable()->after('maneja_calificacion');
            }
        });
    }

    public function down()
    {
        if ( !Schema::hasTable('sga_curso_tiene_asignaturas') ) {
            return;
        }

        Schema::table('sga_curso_tiene_asignaturas', function (Blueprint $table) {
            if ( Schema::hasColumn('sga_curso_tiene_asignaturas', 'cantidad_guias') ) {
                $table->dropColumn('cantidad_guias');
            }
        });
    }
}
