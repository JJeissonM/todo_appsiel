<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnandchangecontratostable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->unsignedInteger('contratante_id')->nullable()->change();
            $table->string('contratanteText')->after('pie_cuatro');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->dropColumn('contratanteText');
        });
    }
}
