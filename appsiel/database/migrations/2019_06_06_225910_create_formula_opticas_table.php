<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormulaOpticasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_formulas_opticas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('paciente_id')->unsigned()->index();
            $table->integer('consulta_id')->unsigned()->index();
            $table->integer('examen_a_mostrar_id')->unsigned()->index();
            $table->string('proximo_control');
            $table->string('tipo_de_lentes');
            $table->string('material');
            $table->string('recomendaciones');
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
        Schema::drop('salud_formulas_opticas');
    }
}
