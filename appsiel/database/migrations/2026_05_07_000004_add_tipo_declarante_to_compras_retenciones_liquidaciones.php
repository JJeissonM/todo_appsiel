<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddTipoDeclaranteToComprasRetencionesLiquidaciones extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_retenciones_liquidaciones')) {
            return;
        }

        Schema::table('compras_retenciones_liquidaciones', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_retenciones_liquidaciones', 'tipo_declarante')) {
                $table->string('tipo_declarante', 40)->default('cualquiera')->after('tipo_operacion');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_retenciones_liquidaciones')) {
            return;
        }

        Schema::table('compras_retenciones_liquidaciones', function (Blueprint $table) {
            if (Schema::hasColumn('compras_retenciones_liquidaciones', 'tipo_declarante')) {
                $table->dropColumn('tipo_declarante');
            }
        });
    }
}
