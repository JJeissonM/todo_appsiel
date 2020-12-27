<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_priceitems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('imagen_cabecera');
            $table->string('precio');
            $table->string('text_color', 40);
            $table->string('button_color', 40);
            $table->string('button2_color', 40);
            $table->string('background_color', 40);
            $table->string('url');
            $table->text('lista_items')->default(null);
            $table->unsignedInteger('price_id');
            $table->foreign('price_id')->references('id')->on('pw_prices')->onDelete('CASCADE');
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
        Schema::drop('pw_priceitems');
    }
}
