<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGrupoEmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_grupos_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('grupo_padre_id')->unsigned()->index();
            $table->string('descripcion');
            $table->string('nombre_corto');
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
        Schema::drop('nom_grupos_empleados');
    }
}
