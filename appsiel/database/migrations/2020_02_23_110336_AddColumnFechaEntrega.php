<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFechaEntrega extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vtas_doc_encabezados', function(Blueprint $table){
          $table->timestamp('fecha_entrega')->after('forma_pago');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vtas_doc_encabezados', function(Blueprint $table){
            $table->dropColumn('fecha_entrega');
        });
    }
}
