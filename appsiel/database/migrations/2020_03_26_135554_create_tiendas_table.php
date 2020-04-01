<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTiendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_tiendas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ubicacion')->nullable();
            $table->string('direccion1');
            $table->string('direccion2')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('pais')->nullable();
            $table->integer('codigo_postal')->nullable();
            $table->longText('terminos_condiciones')->nullable();
            $table->enum('activarimpuesto',['SI','NO'])->default('NO');
            $table->enum('comportamiento_carrito',['REDIRIGIR','AJAX'])->default('AJAX');
            $table->string('unidad_peso')->nullable();
            $table->string('unidad_dimensiones')->nullable();
            $table->enum('aviso_poca_exitencia',['SI','NO'])->default('SI');
            $table->enum('aviso_inventario_agotado',['SI','NO'])->default('SI');
            $table->string('email_destinatario')->nullable();
            $table->integer('umbral_existencia')->nullable();
            $table->integer('umbral_inventario_agotado')->default(0);
            $table->enum('visibilidad_inv_agotado',['SI','NO'])->default('NO');
            $table->enum('mostrar_inventario',['POCA','NUNCA','SIEMPRE'])->default('NUNCA');
            $table->unsignedInteger('widget_id');
            $table->foreign('widget_id')->references('id')->on('pw_widget')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('pw_tiendas');
    }
}
