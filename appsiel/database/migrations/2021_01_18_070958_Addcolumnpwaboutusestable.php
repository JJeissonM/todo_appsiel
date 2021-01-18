<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnpwaboutusestable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_aboutuses', function (Blueprint $table) {
            $table->string('mostrar_leermas', 5)->after('direccion')->default('SI');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_aboutuses', function (Blueprint $table) {
            $table->dropColumn('mostrar_leermas');
        });
    }
}
