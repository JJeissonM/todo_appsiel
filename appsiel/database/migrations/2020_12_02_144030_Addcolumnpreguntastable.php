<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnpreguntastable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_preguntas', function (Blueprint $table) {
            $table->string('tipo_fondo', 20)->after('imagen_fondo');
            $table->string('fondo')->after('tipo_fondo');
            $table->string('repetir')->nullable()->after('fondo');
            $table->string('direccion')->nullable()->after('repetir');
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
            $table->dropColumn('tipo_fondo');
            $table->dropColumn('fondo');
            $table->dropColumn('repetir');
            $table->dropColumn('direccion');
        });
    }
}
