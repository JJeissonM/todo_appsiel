<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgaCatalogoAspectosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_catalogo_aspectos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_tipo_aspecto')->unsigned()->index();
            $table->string('descripcion');
            $table->integer('orden');
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
        Schema::drop('sga_catalogo_aspectos');
    }
}
