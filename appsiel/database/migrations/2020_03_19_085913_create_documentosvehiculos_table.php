<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentosvehiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_documentosvehiculos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tarjeta_operacion', 5)->default('NO'); //SI, NO
            $table->string('documento'); //seguro, tarjeta operación, tecnomecanica, contrato, etc
            $table->string('recurso')->default('NO'); //NO, nombre del recurso (ejemplo: licencia.pdf)
            $table->string('nro_documento');
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fin');
            $table->unsignedInteger('vehiculo_id'); //vehiculo
            $table->foreign('vehiculo_id')->references('id')->on('cte_vehiculos')->onDelete('CASCADE');
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
        Schema::drop('documentosvehiculos');
    }
}
