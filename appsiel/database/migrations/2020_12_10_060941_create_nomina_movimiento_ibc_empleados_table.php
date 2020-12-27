<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaMovimientoIbcEmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_movimientos_ibc_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_mes');
            $table->double('valor_ibc_mes');
            $table->longtext('observaciones');
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('nom_movimientos_ibc_empleados');
    }
}
