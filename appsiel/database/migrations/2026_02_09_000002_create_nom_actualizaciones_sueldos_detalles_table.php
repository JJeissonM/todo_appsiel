<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomActualizacionesSueldosDetallesTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nom_actualizaciones_sueldos_detalles')) {
            return;
        }

        Schema::create('nom_actualizaciones_sueldos_detalles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_actualizacion_sueldo_id');
            $table->integer('nom_contrato_id');
            $table->decimal('salario_anterior', 18, 2)->default(0);
            $table->decimal('salario_nuevo', 18, 2)->default(0);
            $table->tinyInteger('aplicado')->default(1);
            $table->tinyInteger('revertido')->default(0);
            $table->timestamps();

            $table->index('nom_actualizacion_sueldo_id', 'idx_nom_act_sueldo_id');
            $table->index('nom_contrato_id', 'idx_nom_act_contrato_id');
        });
    }

    public function down()
    {
        if (Schema::hasTable('nom_actualizaciones_sueldos_detalles')) {
            Schema::drop('nom_actualizaciones_sueldos_detalles');
        }
    }
}
