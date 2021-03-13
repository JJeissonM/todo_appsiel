<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifycolumnPwitempreguntas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('pw_itempreguntas', function ($table) {
            $table->text('respuesta')->after('pregunta')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('pw_itempreguntas', function ($table) {
            $table->dropColumn('respuesta');
        });
    }
}
