<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Changecolumnscontratostable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->string('codigo')->nullable()->change();
            $table->string('version')->nullable()->change();
            $table->date('fecha')->nullable()->change();
            $table->string('contratanteIdentificacion')->after('contratanteText');
            $table->string('contratanteDireccion')->after('contratanteIdentificacion');
            $table->string('contratanteTelefono')->after('contratanteDireccion');
            $table->integer('anio_contrato')->after('mes_contrato');
            $table->string('tipo_servicio')->after('anio_contrato'); //IDA-REGRESO, IDA, REGRESO
            $table->integer('nro_personas')->after('tipo_servicio');
            $table->string('disponibilidad', 5)->default('SI')->after('nro_personas'); // SI, NO
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->dropColumn('contratanteIdentificacion');
            $table->dropColumn('contratanteDireccion');
            $table->dropColumn('contratanteTelefono');
            $table->dropColumn('anio_contrato');
            $table->dropColumn('tipo_servicio');
            $table->dropColumn('nro_personas');
            $table->dropColumn('disponibilidad');
        });
    }
}
