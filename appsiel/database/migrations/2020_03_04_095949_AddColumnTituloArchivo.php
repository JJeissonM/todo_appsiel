<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTituloArchivo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_archivoitems', function (Blueprint $table) {
            $table->string('titulo')->after('id');
            $table->string('descripcion')->after('titulo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_archivoitems', function (Blueprint $table) {
            $table->dropColumn('titulo');
            $table->dropColumn('descripcion');
        });
    }
}
