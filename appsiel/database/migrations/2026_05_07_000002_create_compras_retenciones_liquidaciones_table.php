<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateComprasRetencionesLiquidacionesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('compras_retenciones_liquidaciones')) {
            return;
        }

        Schema::create('compras_retenciones_liquidaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('compras_doc_encabezado_id');
            $table->unsignedInteger('compras_doc_registro_id')->default(0);
            $table->unsignedInteger('contab_registro_retencion_id')->default(0);
            $table->unsignedInteger('retencion_fuente_concepto_anual_id')->default(0);
            $table->unsignedInteger('contab_retencion_id')->default(0);
            $table->unsignedSmallInteger('anio');
            $table->string('codigo_concepto', 80);
            $table->string('concepto', 255);
            $table->string('tipo_operacion', 60)->default('compras');
            $table->string('tipo_declarante', 40)->default('cualquiera');
            $table->decimal('base_retencion', 15, 2)->default(0);
            $table->decimal('tasa_retencion', 8, 4)->default(0);
            $table->decimal('cuantia_minima_uvt', 10, 2)->default(0);
            $table->decimal('cuantia_minima_pesos', 15, 2)->default(0);
            $table->decimal('valor_retencion', 15, 2)->default(0);
            $table->tinyInteger('aplicada')->default(0);
            $table->string('origen', 40)->default('automatico');
            $table->text('detalle')->nullable();
            $table->string('creado_por', 100)->default('');
            $table->string('modificado_por', 100)->default('');
            $table->string('estado', 40)->default('Activo');
            $table->timestamps();

            $table->index(['compras_doc_encabezado_id', 'estado'], 'compras_ret_liq_doc_idx');
            $table->index(['compras_doc_registro_id', 'estado'], 'compras_ret_liq_linea_idx');
            $table->index(['contab_registro_retencion_id'], 'compras_ret_liq_regcontab_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras_retenciones_liquidaciones');
    }
}
