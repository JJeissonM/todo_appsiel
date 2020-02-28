<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormcontactenosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_formcontactenos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('names');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->string('state', 50); //READ, UNREAD
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
        Schema::drop('formcontactenos');
    }
}
