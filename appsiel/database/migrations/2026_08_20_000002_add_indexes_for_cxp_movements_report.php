<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddIndexesForCxpMovementsReport extends Migration
{
    public function up()
    {
        Schema::table('cxp_movimientos', function (Blueprint $table) {
            $table->index(
                ['core_empresa_id', 'core_tercero_id', 'fecha'],
                'cxp_mov_empresa_tercero_fecha_idx'
            );
        });

        Schema::table('cxp_abonos', function (Blueprint $table) {
            $table->index(
                ['core_empresa_id', 'core_tercero_id', 'doc_cruce_transacc_id', 'fecha'],
                'cxp_abono_empresa_tercero_cruce_fecha_idx'
            );
        });
    }

    public function down()
    {
        Schema::table('cxp_movimientos', function (Blueprint $table) {
            $table->dropIndex('cxp_mov_empresa_tercero_fecha_idx');
        });

        Schema::table('cxp_abonos', function (Blueprint $table) {
            $table->dropIndex('cxp_abono_empresa_tercero_cruce_fecha_idx');
        });
    }
}
