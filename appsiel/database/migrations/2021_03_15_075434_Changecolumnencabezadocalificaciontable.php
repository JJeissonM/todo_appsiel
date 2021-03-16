<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Changecolumnencabezadocalificaciontable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sga_calificaciones_encabezados', function (Blueprint $table) {
            $table->double('peso', 4, 2)->after('descripcion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sga_calificaciones_encabezados', function (Blueprint $table) {
            $table->dropColumn('peso');
        });
    }
}
