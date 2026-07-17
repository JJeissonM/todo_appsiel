<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddInclusionFieldsToAcademicStudentsAndMetas extends Migration
{
    public function up()
    {
        $this->addBooleanColumn('sga_estudiantes', 'es_de_inclusion', 'diagnostico');
        $this->addBooleanColumn('sga_metas', 'es_para_inclusion', 'descripcion');

        $this->backfillInclusionFromLegacyEav();
    }

    public function down()
    {
        $this->dropColumn('sga_metas', 'es_para_inclusion');
        $this->dropColumn('sga_estudiantes', 'es_de_inclusion');
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

    private function dropColumn($tableName, $columnName)
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, $columnName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columnName) {
            $table->dropColumn($columnName);
        });
    }

    private function backfillInclusionFromLegacyEav()
    {
        if (!Schema::hasTable('core_eav_valores') || !Schema::hasTable('sga_inscripciones')) {
            return;
        }

        $legacyValues = DB::table('core_eav_valores')
            ->where('modelo_padre_id', 323)
            ->where('core_campo_id', 1570)
            ->whereIn('valor', ['Si', 'Sí', '1', 1])
            ->pluck('registro_modelo_padre_id');

        if (is_object($legacyValues) && method_exists($legacyValues, 'toArray')) {
            $legacyValues = $legacyValues->toArray();
        }

        if (empty($legacyValues)) {
            return;
        }

        if (Schema::hasTable('sga_estudiantes')) {
            DB::table('sga_estudiantes')
                ->join('sga_inscripciones', 'sga_inscripciones.core_tercero_id', '=', 'sga_estudiantes.core_tercero_id')
                ->whereIn('sga_inscripciones.id', $legacyValues)
                ->update(['sga_estudiantes.es_de_inclusion' => 1]);
        }
    }
}
