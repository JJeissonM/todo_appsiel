<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMinStocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_min_stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inv_bodega_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->double('stock_minimo');
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
        Schema::drop('inv_min_stocks');
    }
}
