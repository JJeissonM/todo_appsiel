<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToVtasPosDocEncabezadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vtas_pos_doc_encabezados', function (Blueprint $table) {
            $table->double('valor_ajuste_al_peso')->after('valor_total');
            $table->double('efectivo_recibido',10)->after('valor_ajuste_al_peso');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vtas_pos_doc_encabezados', function (Blueprint $table) {
            //
        });
    }
}
