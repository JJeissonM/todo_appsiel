<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrestacionesLiquidadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_prestaciones_liquidadas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_promedios');
            $table->longtext('prestaciones_liquidadas');
            $table->longtext('datos_liquidacion');
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
        Schema::drop('nom_prestaciones_liquidadas');
    }
}
