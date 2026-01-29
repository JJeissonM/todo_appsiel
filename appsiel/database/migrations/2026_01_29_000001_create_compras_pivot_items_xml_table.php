<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprasPivotItemsXmlTable extends Migration
{
    public function up()
    {
        // Ensure referenced tables exist before attempting to create the pivot table
        if (!Schema::hasTable('inv_productos') || !Schema::hasTable('compras_proveedores')) {
            return;
        }

        Schema::create('compras_pivot_items_xml', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('inv_producto_id');
            $table->string('codigo_item_xml');
            $table->string('nombre_item_xml');
            $table->unsignedBigInteger('proveedor_id');
            $table->timestamps();

            $table->foreign('inv_producto_id')
                ->references('id')
                ->on('inv_productos')
                ->onDelete('cascade');

            $table->foreign('proveedor_id')
                ->references('id')
                ->on('compras_proveedores')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_pivot_items_xml')) {
            return;
        }

        Schema::dropIfExists('compras_pivot_items_xml');
    }
}
