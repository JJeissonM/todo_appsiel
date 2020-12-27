<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomPrestamosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_prestamos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->date('fecha_inicio');
            $table->double('valor_prestamo');
            $table->double('valor_cuota');
            $table->integer('numero_cuotas');
            $table->double('valor_acumulado');
            $table->longtext('detalle');
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
        Schema::drop('nom_prestamos');
    }
}
