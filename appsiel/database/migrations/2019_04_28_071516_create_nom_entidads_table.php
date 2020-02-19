<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomEntidadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_entidades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->string('descripcion');
            $table->string('codigo_nacional');
            $table->string('tipo_entidad');
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
        Schema::drop('nom_entidades');
    }
}
