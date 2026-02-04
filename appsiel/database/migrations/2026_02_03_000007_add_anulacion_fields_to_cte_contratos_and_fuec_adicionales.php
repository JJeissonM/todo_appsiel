<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAnulacionFieldsToCteContratosAndFuecAdicionales extends Migration
{
    public function up()
    {
        if (Schema::hasTable('cte_contratos')) {
            Schema::table('cte_contratos', function (Blueprint $table) {
                if (!Schema::hasColumn('cte_contratos', 'anulacion_motivo')) {
                    $table->text('anulacion_motivo')->nullable();
                }
                if (!Schema::hasColumn('cte_contratos', 'anulado_por')) {
                    $table->unsignedInteger('anulado_por')->nullable();
                }
                if (!Schema::hasColumn('cte_contratos', 'anulado_el')) {
                    $table->timestamp('anulado_el')->nullable();
                }
            });
        }

        if (Schema::hasTable('cte_fuec_adicionales')) {
            Schema::table('cte_fuec_adicionales', function (Blueprint $table) {
                if (!Schema::hasColumn('cte_fuec_adicionales', 'anulacion_motivo')) {
                    $table->text('anulacion_motivo')->nullable();
                }
                if (!Schema::hasColumn('cte_fuec_adicionales', 'anulado_por')) {
                    $table->unsignedInteger('anulado_por')->nullable();
                }
                if (!Schema::hasColumn('cte_fuec_adicionales', 'anulado_el')) {
                    $table->timestamp('anulado_el')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cte_contratos')) {
            Schema::table('cte_contratos', function (Blueprint $table) {
                if (Schema::hasColumn('cte_contratos', 'anulacion_motivo')) {
                    $table->dropColumn('anulacion_motivo');
                }
                if (Schema::hasColumn('cte_contratos', 'anulado_por')) {
                    $table->dropColumn('anulado_por');
                }
                if (Schema::hasColumn('cte_contratos', 'anulado_el')) {
                    $table->dropColumn('anulado_el');
                }
            });
        }

        if (Schema::hasTable('cte_fuec_adicionales')) {
            Schema::table('cte_fuec_adicionales', function (Blueprint $table) {
                if (Schema::hasColumn('cte_fuec_adicionales', 'anulacion_motivo')) {
                    $table->dropColumn('anulacion_motivo');
                }
                if (Schema::hasColumn('cte_fuec_adicionales', 'anulado_por')) {
                    $table->dropColumn('anulado_por');
                }
                if (Schema::hasColumn('cte_fuec_adicionales', 'anulado_el')) {
                    $table->dropColumn('anulado_el');
                }
            });
        }
    }
}
