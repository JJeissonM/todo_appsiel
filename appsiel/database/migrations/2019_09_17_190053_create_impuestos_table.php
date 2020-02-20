<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImpuestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contab_impuestos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->double('tasa_impuesto');
            $table->integer('cta_ventas_id')->unsigned()->index();
            $table->integer('cta_ventas_devol_id')->unsigned()->index();
            $table->integer('cta_compras_id')->unsigned()->index();
            $table->integer('cta_compras_devol_id')->unsigned()->index();
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
        Schema::drop('contab_impuestos');
    }
}
