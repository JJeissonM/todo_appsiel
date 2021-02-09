<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametrosRetefuenteEmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_parametros_retefuente_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_promedios');
            $table->integer('procedimiento');
            $table->double('valor_base_depurada');
            $table->double('renta_trabajo_exenta');
            $table->double('sub_total');
            $table->double('base_retencion_pesos');
            $table->double('base_retencion_uvts');
            $table->string('rango_tabla');
            $table->double('valor_retencion_uvts');
            $table->double('porcentaje_fijo');
            $table->double('deduccion_pago_terceros_alimentacion');
            $table->double('deduccion_viaticos_ocacionales');
            $table->double('deduccion_medios_transporte');
            $table->double('deduccion_aportes_pension_voluntaria');
            $table->double('deduccion_ahorros_cuentas_afc');
            $table->double('deduccion_rentas_trabajo_exentas');
            $table->double('deduccion_intereses_vivienda');
            $table->double('deduccion_salud_prepagada');
            $table->boolean('deduccion_por_dependientes');
            $table->string('estado');
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
        Schema::drop('nom_parametros_retefuente_empleados');
    }
}
