<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LinkCxcPaymentsToSourceMovement extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cxc_abonos') && !Schema::hasColumn('cxc_abonos','cxc_movimiento_id')) {
            Schema::table('cxc_abonos', function (Blueprint $table) {
                $table->unsignedInteger('cxc_movimiento_id')->nullable()->index();
            });
        }
        if (Schema::hasTable('cxc_movimientos') && !Schema::hasColumn('cxc_movimientos','contab_cuenta_id')) {
            Schema::table('cxc_movimientos', function (Blueprint $table) {
                $table->unsignedInteger('contab_cuenta_id')->nullable()->index();
            });
        }
        // Los datos historicos ambiguos no se enlazan por numero de documento.
    }

    public function down()
    {
        foreach (['cxc_abonos'=>'cxc_movimiento_id','cxc_movimientos'=>'contab_cuenta_id'] as $tabla=>$columna) {
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla,$columna)) {
                Schema::table($tabla, function (Blueprint $table) use ($columna) {
                    $table->dropIndex([$columna]);
                    $table->dropColumn($columna);
                });
            }
        }
    }
}
