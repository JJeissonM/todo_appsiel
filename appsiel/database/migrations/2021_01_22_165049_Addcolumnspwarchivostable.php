<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnspwarchivostable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_archivos', function (Blueprint $table) {
            $table->string('tipo_fondo', 20)->after('descripcion');
            $table->string('fondo')->after('tipo_fondo');
            $table->string('repetir')->nullable()->after('fondo');
            $table->string('direccion')->nullable()->after('repetir');
            $table->unsignedInteger('configuracionfuente_id')->after('direccion')->nullable();
            $table->foreign('configuracionfuente_id')->references('id')->on('pw_configuracionfuentes')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_archivos', function (Blueprint $table) {
            $table->dropColumn('configuracionfuente_id');
            $table->dropColumn('tipo_fondo');
            $table->dropColumn('fondo');
            $table->dropColumn('repetir');
            $table->dropColumn('direccion');
        });
    }
}
