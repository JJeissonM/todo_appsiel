<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropRedundantInclusionFieldsFromInscriptionsAndEnrollments extends Migration
{
    public function up()
    {
        $this->syncRedundantValuesToStudents();

        $this->dropColumn('sga_inscripciones', 'diagnostico_inclusion');
        $this->dropColumn('sga_inscripciones', 'es_de_inclusion');
        $this->dropColumn('sga_matriculas', 'diagnostico_inclusion');
        $this->dropColumn('sga_matriculas', 'es_de_inclusion');
    }

    public function down()
    {
        $this->addBooleanColumn('sga_inscripciones', 'es_de_inclusion', 'estado');
        $this->addTextColumn('sga_inscripciones', 'diagnostico_inclusion', 'es_de_inclusion');
        $this->addBooleanColumn('sga_matriculas', 'es_de_inclusion', 'observacion_general');
        $this->addTextColumn('sga_matriculas', 'diagnostico_inclusion', 'es_de_inclusion');
    }

    private function addBooleanColumn($tableName, $columnName, $afterColumn)
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $afterColumn) {
            $table->boolean($columnName)->default(0)->after($afterColumn);
        });
    }

    private function addTextColumn($tableName, $columnName, $afterColumn)
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName, $afterColumn) {
            $table->text($columnName)->nullable()->after($afterColumn);
        });
    }

    private function dropColumn($tableName, $columnName)
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropColumn($columnName);
        });
    }

    private function syncRedundantValuesToStudents()
    {
        if (!Schema::hasTable('sga_estudiantes')) {
            return;
        }

        if (Schema::hasTable('sga_inscripciones')) {
            if (Schema::hasColumn('sga_estudiantes', 'es_de_inclusion') && Schema::hasColumn('sga_inscripciones', 'es_de_inclusion')) {
                DB::table('sga_estudiantes')
                    ->join('sga_inscripciones', 'sga_inscripciones.core_tercero_id', '=', 'sga_estudiantes.core_tercero_id')
                    ->where('sga_inscripciones.es_de_inclusion', 1)
                    ->update(['sga_estudiantes.es_de_inclusion' => 1]);
            }

            if (Schema::hasColumn('sga_estudiantes', 'diagnostico_inclusion') && Schema::hasColumn('sga_inscripciones', 'diagnostico_inclusion')) {
                DB::table('sga_estudiantes')
                    ->join('sga_inscripciones', 'sga_inscripciones.core_tercero_id', '=', 'sga_estudiantes.core_tercero_id')
                    ->whereNotNull('sga_inscripciones.diagnostico_inclusion')
                    ->where('sga_inscripciones.diagnostico_inclusion', '<>', '')
                    ->update(['sga_estudiantes.diagnostico_inclusion' => DB::raw('sga_inscripciones.diagnostico_inclusion')]);
            }
        }

        if (Schema::hasTable('sga_matriculas')) {
            if (Schema::hasColumn('sga_estudiantes', 'es_de_inclusion') && Schema::hasColumn('sga_matriculas', 'es_de_inclusion')) {
                DB::table('sga_estudiantes')
                    ->join('sga_matriculas', 'sga_matriculas.id_estudiante', '=', 'sga_estudiantes.id')
                    ->where('sga_matriculas.es_de_inclusion', 1)
                    ->update(['sga_estudiantes.es_de_inclusion' => 1]);
            }

            if (Schema::hasColumn('sga_estudiantes', 'diagnostico_inclusion') && Schema::hasColumn('sga_matriculas', 'diagnostico_inclusion')) {
                DB::table('sga_estudiantes')
                    ->join('sga_matriculas', 'sga_matriculas.id_estudiante', '=', 'sga_estudiantes.id')
                    ->whereNotNull('sga_matriculas.diagnostico_inclusion')
                    ->where('sga_matriculas.diagnostico_inclusion', '<>', '')
                    ->update(['sga_estudiantes.diagnostico_inclusion' => DB::raw('sga_matriculas.diagnostico_inclusion')]);
            }
        }
    }
}
