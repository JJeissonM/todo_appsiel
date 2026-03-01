<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTesoChequerasTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('teso_chequeras')) {
            return;
        }

        Schema::create('teso_chequeras', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('teso_cuenta_bancaria_id');
            $table->string('descripcion');
            $table->unsignedInteger('numero_inicial');
            $table->unsignedInteger('numero_final');
            $table->unsignedInteger('consecutivo_actual');
            $table->string('estado')->default('Activo');
            $table->timestamps();

            $table->foreign('teso_cuenta_bancaria_id', 'fk_teso_chequeras_cuenta')
                ->references('id')
                ->on('teso_cuentas_bancarias')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('teso_chequeras')) {
            return;
        }

        Schema::table('teso_chequeras', function (Blueprint $table) {
            $table->dropForeign('fk_teso_chequeras_cuenta');
        });

        Schema::dropIfExists('teso_chequeras');
    }
}
