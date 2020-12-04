<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnitempreguntastable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_preguntas', function (Blueprint $table) {
            $table->string('color1', 20)->after('descripcion');
            $table->string('color2', 20)->after('color1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_preguntas', function (Blueprint $table) {
            $table->dropColumn('color1');
            $table->dropColumn('color2');
        });
    }
}
