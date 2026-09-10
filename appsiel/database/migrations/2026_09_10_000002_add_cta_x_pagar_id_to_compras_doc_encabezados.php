<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCtaXPagarIdToComprasDocEncabezados extends Migration
{
    public function up()
    {
        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->unsignedInteger('cta_x_pagar_id')->nullable();
            $table->foreign('cta_x_pagar_id', 'compras_cuenta_directa_fk')->references('id')->on('contab_cuentas');
        });
    }

    public function down()
    {
        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->dropForeign('compras_cuenta_directa_fk');
            $table->dropColumn('cta_x_pagar_id');
        });
    }
}
