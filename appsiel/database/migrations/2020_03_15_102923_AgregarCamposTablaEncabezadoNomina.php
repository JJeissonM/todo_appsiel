<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregarCamposTablaEncabezadoNomina extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nom_doc_encabezados', function(Blueprint $table){
          $table->string('tipo_liquidacion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nom_doc_encabezados', function(Blueprint $table){
            $table->dropColumn('tipo_liquidacion');
        });
    }
}
