<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExcluirDocumentosNominaElectronicaToNomContratos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('nom_contratos')) {
            Schema::table('nom_contratos', function (Blueprint $table) {
                $table->boolean('excluir_documentos_nomina_electronica')->default(false)->after('tipo_cotizante');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('nom_contratos')) {
            Schema::table('nom_contratos', function (Blueprint $table) {
                $table->dropColumn('excluir_documentos_nomina_electronica');
            });
        }
    }
}
