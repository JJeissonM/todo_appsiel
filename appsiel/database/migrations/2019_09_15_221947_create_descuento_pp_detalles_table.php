<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDescuentoPpDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_descuentos_pp_detalles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('encabezado_id')->unsigned()->index();
            $table->integer('dias_pp');
            $table->double('descuento_pp');
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
        Schema::drop('vtas_descuentos_pp_detalles');
    }
}
