<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateComprasRetencionFuenteConceptosAnualesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('compras_retencion_fuente_conceptos_anuales')) {
            return;
        }

        Schema::create('compras_retencion_fuente_conceptos_anuales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('anio');
            $table->decimal('uvt', 15, 2)->default(0);
            $table->string('codigo', 80);
            $table->string('concepto', 255);
            $table->string('tipo_operacion', 60)->default('compras');
            $table->string('tipo_item', 60)->default('producto');
            $table->string('tipo_declarante', 40)->default('cualquiera');
            $table->decimal('tasa_retencion', 8, 4)->default(0);
            $table->decimal('cuantia_minima_uvt', 10, 2)->default(0);
            $table->decimal('cuantia_minima_pesos', 15, 2)->default(0);
            $table->string('base_calculo', 60)->default('sin_iva');
            $table->unsignedInteger('contab_retencion_id')->default(0);
            $table->string('estado', 40)->default('Activo');
            $table->timestamps();

            $table->unique(['anio', 'codigo'], 'compras_ret_fuente_anio_codigo_unique');
            $table->index(['anio', 'tipo_item', 'estado'], 'compras_ret_fuente_busqueda_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras_retencion_fuente_conceptos_anuales');
    }
}
