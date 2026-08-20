<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddIndexesForCxcMovementsReport extends Migration
{
    public function up()
    {
        Schema::table('cxc_movimientos', function (Blueprint $table) {
            $table->index(
                ['core_empresa_id', 'core_tercero_id', 'fecha'],
                'cxc_mov_empresa_tercero_fecha_idx'
            );
        });

        Schema::table('cxc_abonos', function (Blueprint $table) {
            $table->index(
                ['core_empresa_id', 'core_tercero_id', 'doc_cruce_transacc_id', 'fecha'],
                'cxc_abono_empresa_tercero_cruce_fecha_idx'
            );
        });
    }

    public function down()
    {
        Schema::table('cxc_movimientos', function (Blueprint $table) {
            $table->dropIndex('cxc_mov_empresa_tercero_fecha_idx');
        });

        Schema::table('cxc_abonos', function (Blueprint $table) {
            $table->dropIndex('cxc_abono_empresa_tercero_cruce_fecha_idx');
        });
    }
}
