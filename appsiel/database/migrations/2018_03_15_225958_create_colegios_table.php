<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColegiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colegios', function (Blueprint $table) {
            $table->increments('id');
			$table->string('descripcion');
			$table->string('slogan');
			$table->string('resolucion');
			$table->string('direccion');
			$table->string('telefonos');
			$table->string('piefirma1');
			$table->string('piefirma2');
			$table->string('escudo');
			$table->string('maneja_puesto');
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
        Schema::drop('colegios');
    }
}
