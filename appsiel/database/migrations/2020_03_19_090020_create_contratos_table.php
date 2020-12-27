<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('version');
            $table->date('fecha');
            $table->string('numero_contrato');
            $table->string('objeto');
            $table->integer('origen'); //id ciudad origen
            $table->integer('destino'); // id ciudad destino
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->double('valor_contrato');
            $table->double('valor_empresa');
            $table->double('valor_propietario');
            $table->string('direccion_notificacion');
            $table->string('telefono_notificacion');
            $table->integer('dia_contrato');
            $table->string('mes_contrato');
            $table->string('pie_uno');
            $table->string('pie_dos');
            $table->string('pie_tres');
            $table->string('pie_cuatro');
            $table->unsignedInteger('contratante_id'); //contratante
            $table->foreign('contratante_id')->references('id')->on('cte_contratantes')->onDelete('CASCADE');
            $table->unsignedInteger('vehiculo_id'); //vehiculo
            $table->foreign('vehiculo_id')->references('id')->on('cte_vehiculos')->onDelete('CASCADE');
            $table->unsignedInteger('conductor_id')->nullable(); //conductor cuando es el quien crea el contrato
            $table->foreign('conductor_id')->references('id')->on('cte_conductors')->onDelete('CASCADE');
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
        Schema::drop('contratos');
    }
}
