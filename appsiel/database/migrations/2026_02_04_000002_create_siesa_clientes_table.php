<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaClientesTable extends Migration
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
        if (!Schema::hasTable('siesa_clientes')) {
            Schema::create('siesa_clientes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('id_cliente')->nullable();
                $table->string('nombre_cliente')->nullable();
                $table->string('centro_operacion_cliente')->nullable();
                $table->string('estado_cliente')->nullable();
                $table->string('lista_precio_cliente')->nullable();
                $table->string('ld')->nullable();
                $table->string('sucursal_cliente')->nullable();
                $table->string('cliente_en_enterprise')->nullable();
                $table->string('columna1')->nullable();
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
        if (Schema::hasTable('siesa_clientes')) {
            Schema::drop('siesa_clientes');
        }
    }
}


