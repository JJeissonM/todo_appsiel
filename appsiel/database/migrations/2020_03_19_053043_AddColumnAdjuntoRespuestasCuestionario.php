<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAdjuntoRespuestasCuestionario extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sga_respuestas_cuestionarios', function(Blueprint $table){
          $table->string('adjunto')->after('calificacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sga_respuestas_cuestionarios', function(Blueprint $table){
            $table->dropColumn('adjunto');
        });
    }
}
