<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDiagnosticoInclusionToAcademicStudents extends Migration
{
    public function up()
    {
        $this->addTextColumn('sga_estudiantes', 'diagnostico_inclusion', 'es_de_inclusion');

        $this->backfillDiagnosticoFromLegacyEav();
    }

    public function down()
    {
        $this->dropColumn('sga_estudiantes', 'diagnostico_inclusion');
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

    private function backfillDiagnosticoFromLegacyEav()
    {
        if (!Schema::hasTable('core_eav_valores') || !Schema::hasTable('sga_inscripciones')) {
            return;
        }

        $legacyValues = DB::table('core_eav_valores')
            ->where('modelo_padre_id', 323)
            ->where('core_campo_id', 1571)
            ->whereNotNull('valor')
            ->where('valor', '<>', '')
            ->select('registro_modelo_padre_id', 'valor')
            ->get();

        foreach ($legacyValues as $legacyValue) {
            $inscripcionId = (int)$legacyValue->registro_modelo_padre_id;
            $diagnostico = $legacyValue->valor;

            if (Schema::hasTable('sga_estudiantes')) {
                DB::table('sga_estudiantes')
                    ->join('sga_inscripciones', 'sga_inscripciones.core_tercero_id', '=', 'sga_estudiantes.core_tercero_id')
                    ->where('sga_inscripciones.id', $inscripcionId)
                    ->update(['sga_estudiantes.diagnostico_inclusion' => $diagnostico]);
            }
        }

    }
}
