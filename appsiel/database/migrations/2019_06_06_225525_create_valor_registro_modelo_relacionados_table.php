<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValorRegistroModeloRelacionadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_valores_relacionados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modelo_principal_id')->unsigned()->index();
            $table->integer('registro_modelo_principal_id')->unsigned()->index();
            $table->integer('modelo_relacionado_id')->unsigned()->index();
            $table->integer('core_campo_id')->unsigned()->index();
            $table->string('valor');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('core_valores_relacionados');
    }
}
