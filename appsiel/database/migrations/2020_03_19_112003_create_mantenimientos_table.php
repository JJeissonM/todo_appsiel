<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMantenimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_mantenimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->string('sede');
            $table->string('revisado');
            $table->unsignedInteger('anioperiodo_id'); //anio
            $table->foreign('anioperiodo_id')->references('id')->on('cte_anioperiodos')->onDelete('CASCADE');
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
        Schema::drop('mantenimientos');
    }
}
