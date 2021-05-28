<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDescuentoProntoPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_descuentos_pronto_pago', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->integer('contab_cuenta_id')->unsigned()->index();
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
        Schema::drop('compras_descuentos_pronto_pago');
    }
}
