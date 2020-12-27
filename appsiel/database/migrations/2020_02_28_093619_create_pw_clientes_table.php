<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePwClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('descripcion');
            $table->string('tipo_fondo', 20);
            $table->string('fondo');
            $table->string('repetir')->nullable();
            $table->string('direccion')->nullable();
            $table->unsignedInteger('widget_id');
            $table->foreign('widget_id')->references('id')->on('pw_widget')->onDelete('CASCADE');
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
        Schema::drop('pw_clientes');
    }
}
