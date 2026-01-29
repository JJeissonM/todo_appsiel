<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTipoIcfesToSgaCuestionarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('sga_cuestionarios')) {
            return;
        }

        Schema::table('sga_cuestionarios', function (Blueprint $table) {
            $table->string('tipo_icfes')->nullable()->after('detalle')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('sga_cuestionarios')) {
            return;
        }

        Schema::table('sga_cuestionarios', function (Blueprint $table) {
            $table->dropColumn('tipo_icfes');
        });
    }
}
