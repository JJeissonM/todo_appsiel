<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaListasDescuentosTable extends Migration
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
        if (!Schema::hasTable('siesa_listas_descuentos')) {
            Schema::create('siesa_listas_descuentos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ld')->nullable();
                $table->string('nombre_ld')->nullable();
                $table->string('referencia_item')->nullable();
                $table->string('nombre_item')->nullable();
                $table->date('fecha')->nullable();
                $table->string('um')->nullable();
                $table->string('valor_max')->nullable();
                $table->double('descuento1', 15, 4)->nullable();
                $table->double('descuento2', 15, 4)->nullable();
                $table->string('items_enterprise')->nullable();
                $table->string('extencion_item_ente')->nullable();
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
        if (Schema::hasTable('siesa_listas_descuentos')) {
            Schema::drop('siesa_listas_descuentos');
        }
    }
}


