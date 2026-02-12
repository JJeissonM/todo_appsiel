<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaEncabezadosListasDescuentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        if (!Schema::hasTable('siesa_encabezados_listas_descuentos')) {
            Schema::create('siesa_encabezados_listas_descuentos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('id_entreprise')->nullable();
                $table->string('lp')->nullable();
                $table->string('descripcion_dscto_promocion')->nullable();
                $table->string('fecha_inicial')->nullable();
                $table->string('fecha_final')->nullable();
                $table->string('estado')->nullable();
                $table->string('exclusivo')->nullable();
                $table->string('exclusivo_para_control_dsctos_manuales')->nullable();
                $table->string('exclusivo_valor_acumulado')->nullable();
                $table->string('notas')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        if (Schema::hasTable('siesa_encabezados_listas_descuentos')) {
            Schema::drop('siesa_encabezados_listas_descuentos');
        }
    }
}


