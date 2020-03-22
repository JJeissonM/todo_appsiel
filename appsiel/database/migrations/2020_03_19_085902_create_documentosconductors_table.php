<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentosconductorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_documentosconductors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('licencia', 5)->default('NO'); //SI, NO
            $table->string('documento'); //licencia, documento identidad, seguro medico, etc
            $table->string('recurso')->default('NO'); //NO, nombre del recurso (ejemplo: licencia.pdf)
            $table->string('nro_documento');
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fin');
            $table->unsignedInteger('conductor_id'); //conductor
            $table->foreign('conductor_id')->references('id')->on('cte_conductors')->onDelete('CASCADE');
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
        Schema::drop('documentosconductors');
    }
}
