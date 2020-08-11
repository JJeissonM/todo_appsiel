<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResponsableestudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_responsableestudiantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('direccion_trabajo');
            $table->string('telefono_trabajo');
            $table->string('puesto_trabajo')->nullable();
            $table->string('empresa_labora')->nullable();
            $table->string('jefe_inmediato')->nullable();
            $table->string('telefono_jefe')->nullable();
            $table->text('descripcion_trabajador_independiente')->nullable();
            $table->string('ocupacion')->nullable();
            $table->Integer('tiporesponsable_id')->unsigned(); //PAPA, MAMA, RESPONSABLE-FINANCIERO
            $table->foreign('tiporesponsable_id')->references('id')->on('sga_tiporesponsables')->onDelete('cascade');
            $table->Integer('estudiante_id')->unsigned();
            $table->foreign('estudiante_id')->references('id')->on('sga_estudiantes')->onDelete('cascade');
            $table->Integer('tercero_id')->unsigned(); //datos de la persona
            $table->foreign('tercero_id')->references('id')->on('core_terceros')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sga_responsableestudiantes');
    }
}
