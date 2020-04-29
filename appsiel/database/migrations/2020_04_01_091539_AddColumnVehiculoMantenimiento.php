<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnVehiculoMantenimiento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_mantenimientos', function (Blueprint $table) {
            $table->unsignedInteger('vehiculo_id'); //vehiculo
            $table->foreign('vehiculo_id')->references('id')->on('cte_vehiculos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_mantenimientos', function (Blueprint $table) {
            $table->dropColumn('vehiculo_id');
        });
    }
}
