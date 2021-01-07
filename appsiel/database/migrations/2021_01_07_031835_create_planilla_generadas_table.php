<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanillaGeneradasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_planillas_generadas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pila_datos_empresa_id');
            $table->string('descripcion');
            $table->date('fecha_final_mes');
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
        Schema::drop('nom_pila_planillas_generadas');
    }
}
