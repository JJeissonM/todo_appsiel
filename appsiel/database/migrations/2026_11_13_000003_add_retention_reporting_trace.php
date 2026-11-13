<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRetentionReportingTrace extends Migration
{
    public function up()
    {
        $crearIndice = !Schema::hasColumn('contab_registros_retenciones', 'core_empresa_id');
        $existentes = Schema::getColumnListing('contab_registros_retenciones');
        Schema::table('contab_registros_retenciones', function (Blueprint $table) use ($crearIndice, $existentes) {
            foreach (['core_empresa_id', 'core_tercero_id', 'documento_id'] as $campo) {
                if (!in_array($campo, $existentes)) { $table->unsignedInteger($campo)->nullable(); }
            }
            if (!in_array('fecha', $existentes)) { $table->date('fecha')->nullable(); }
            if (!in_array('tipo_persona_retenida', $existentes)) { $table->string('tipo_persona_retenida', 25)->nullable(); }
            if (!in_array('documento_tabla', $existentes)) { $table->string('documento_tabla', 50)->nullable(); }
            if ($crearIndice) { $table->index(['core_empresa_id', 'fecha', 'tipo', 'estado'], 'retenciones_reporte_periodo_idx'); }
        });
        if (!Schema::hasTable('contab_retenciones_reporte_config')) {
            Schema::create('contab_retenciones_reporte_config', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('core_empresa_id');
                $table->unsignedInteger('contab_retencion_id');
                $table->unsignedSmallInteger('anio');
                $table->string('tipo_impuesto', 10);
                $table->string('concepto', 80)->nullable();
                $table->string('codigo_municipio', 10)->nullable();
                $table->string('actividad_ica', 30)->nullable();
                $table->string('modificado_por', 120);
                $table->timestamps();
                $table->unique(['core_empresa_id', 'contab_retencion_id', 'anio'], 'retenciones_config_empresa_anio_unique');
            });
        }
        (new \App\Contabilidad\Services\Retenciones\TrazabilidadRetenciones)->reconstruir();
    }

    public function down()
    {
        Schema::dropIfExists('contab_retenciones_reporte_config');
        Schema::table('contab_registros_retenciones', function (Blueprint $table) {
            $table->dropIndex('retenciones_reporte_periodo_idx');
            $table->dropColumn(['core_empresa_id', 'fecha', 'core_tercero_id', 'tipo_persona_retenida', 'documento_tabla', 'documento_id']);
        });
    }
}
