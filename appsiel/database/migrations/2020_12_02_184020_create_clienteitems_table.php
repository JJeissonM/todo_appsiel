<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClienteitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_clienteitems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('logo');
            $table->string('enlace')->nullable()->default(null);
            $table->unsignedInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('pw_clientes')->onDelete('CASCADE');
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
        Schema::drop('pw_clienteitems');
    }
}
