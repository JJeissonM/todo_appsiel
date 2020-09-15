<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('pw_logins', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->string('ruta')->nullable();
            $table->string('imagen')->nullable();
            $table->string('disposicion',20)->default('DEFAULT');
            $table->unsignedInteger('widget_id');
            $table->foreign('widget_id')->references('id')->on('pw_widget')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::drop('pw_logins');
    }
}
