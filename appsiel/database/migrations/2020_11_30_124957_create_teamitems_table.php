<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_teamitems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description');
            $table->string('more_details');
            $table->string('text_color', 40);
            $table->string('title_color', 40);
            $table->string('background_color', 40);
            $table->string('imagen');
            $table->unsignedInteger('team_id');
            $table->foreign('team_id')->references('id')->on('pw_teams')->onDelete('CASCADE');
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
        Schema::drop('pw_teamitems');
    }
}
