<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarouselsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_mod_carousels', function (Blueprint $table) {
            $table->increments('id');
            $table->longtext('imagenes');
            $table->string('altura_maxima');
            $table->string('descripcion');
            $table->tinyInteger('activar_controles_laterales');
            $table->string('estado');
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
        Schema::drop('pw_mod_carousels');
    }
}
