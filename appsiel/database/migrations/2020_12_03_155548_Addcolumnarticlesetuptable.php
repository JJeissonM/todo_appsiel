<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnarticlesetuptable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_articlesetups', function (Blueprint $table) {
            $table->string('tipo_fondo', 20)->after('orden');
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
        Schema::table('pw_articlesetups', function (Blueprint $table) {
            $table->dropColumn('tipo_fondo');
            $table->dropColumn('fondo');
            $table->dropColumn('repetir');
            $table->dropColumn('direccion');
        });
    }
}
