<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquivalenciaContablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_equivalencias_contables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->integer('nom_grupo_empleado_id')->unsigned()->index();
            $table->integer('contab_cuenta_id')->unsigned()->index();
            $table->string('tipo_movimiento');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('nom_entidad_id')->unsigned()->index();
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
        Schema::drop('nom_equivalencias_contables');
    }
}
