<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvFichaProductoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_ficha_producto', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key');
            $table->longText('descripcion');
            $table->unsignedInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('inv_productos')->onDelete('CASCADE');
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
        Schema::drop('vtas_pos_movimientos');
    }
}
