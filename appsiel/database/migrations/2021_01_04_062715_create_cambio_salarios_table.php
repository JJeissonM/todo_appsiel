<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCambioSalariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_cambios_salarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->double('salario_anterior');
            $table->double('nuevo_salario');
            $table->date('fecha_modificacion');
            $table->string('tipo_modificacion');
            $table->longtext('observacion');
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('nom_cambios_salarios');
    }
}
