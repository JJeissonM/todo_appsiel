<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomCargoTipoTurnoTable extends Migration
{
    public function up()
    {
        Schema::create('nom_cargo_tipo_turno', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cargo_id');
            $table->unsignedInteger('tipo_turno_id');
            $table->timestamps();

            $table->unique(['cargo_id', 'tipo_turno_id']);
            $table->foreign('cargo_id')->references('id')->on('nom_cargos')->onDelete('cascade');
            $table->foreign('tipo_turno_id')->references('id')->on('nom_turnos_tipos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('nom_cargo_tipo_turno');
    }
}
