<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNomActualizacionesSueldosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('nom_actualizaciones_sueldos')) {
            return;
        }

        Schema::create('nom_actualizaciones_sueldos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->nullable();
            $table->integer('grupo_empleado_id')->nullable();
            $table->decimal('porcentaje', 6, 2)->default(0);
            $table->date('fecha')->nullable();
            $table->string('estado')->default('Aplicado');
            $table->text('observacion')->nullable();
            $table->string('creado_por');
            $table->string('modificado_por')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        if (Schema::hasTable('nom_actualizaciones_sueldos')) {
            Schema::drop('nom_actualizaciones_sueldos');
        }
    }
}
