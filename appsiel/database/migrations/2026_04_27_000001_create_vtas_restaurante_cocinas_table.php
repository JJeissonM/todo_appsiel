<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateVtasRestauranteCocinasTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vtas_restaurante_cocinas')) {
            return;
        }

        Schema::create('vtas_restaurante_cocinas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->unsignedInteger('grupo_inventarios_id')->nullable();
            $table->unsignedInteger('bodega_default_id')->nullable();
            $table->string('url_imagen', 500)->nullable();
            $table->string('printer_ip', 100)->nullable();
            $table->string('estado', 20)->default('Activo');
            $table->timestamps();

            $table->index('grupo_inventarios_id', 'idx_vrc_grupo_inventarios');
            $table->index('bodega_default_id', 'idx_vrc_bodega_default');
            $table->index('estado', 'idx_vrc_estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vtas_restaurante_cocinas');
    }
}
