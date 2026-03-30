<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdvIdToTesoMovimientosTable extends Migration
{
    public function up()
    {
        Schema::table('teso_movimientos', function (Blueprint $table) {
            if ( !Schema::hasColumn('teso_movimientos', 'pdv_id') ) {
                $table->unsignedInteger('pdv_id')->nullable()->after('teso_cuenta_bancaria_id');
                $table->index(['pdv_id', 'fecha'], 'teso_movimientos_pdv_fecha_index');
            }
        });
    }

    public function down()
    {
        Schema::table('teso_movimientos', function (Blueprint $table) {
            if ( Schema::hasColumn('teso_movimientos', 'pdv_id') ) {
                $table->dropIndex('teso_movimientos_pdv_fecha_index');
                $table->dropColumn('pdv_id');
            }
        });
    }
}
